<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector as RedirectorAlias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Vonage\Client;
use Vonage\Verify2\Request\SilentAuthRequest;

class SilentController extends Controller
{
    private Client $vonageClient;

    public function __construct()
    {
        $credentials = new Client\Credentials\Keypair(
            file_get_contents(base_path(env('VONAGE_PRIVATE_KEY_PATH'))),
            env('VONAGE_APPLICATION_ID')
        );

        $this->vonageClient = new Client($credentials);
    }

    public function store(Request $request): View|Application|Factory|ApplicationContract
    {
        return view('auth/silent');
    }

    public function start(Request $request): Application|RedirectorAlias|RedirectResponse|ApplicationContract
    {
        $redirectUrl = $request->get('redirect_url');
        $phoneNumber = $request->get('phone_number');

        $twoFactorRequest = new SilentAuthRequest($phoneNumber, 'VONAGE', $redirectUrl);

        try {
            $response = $this->vonageClient->verify2()->startVerification($twoFactorRequest);
        } catch (Client\Exception\Request $e) {
            Log::error($e->getMessage());
            if ($e->getCode() === ResponseAlias::HTTP_CONFLICT) {
                Log::error('409, redirecting to SMS');
                return redirect(route('sms'));
            }
        }

        $request->session()->put('request_id', $response['request_id']);
        $checkUrl = $response['check_url'];

        return redirect($checkUrl);
    }

    public function callback(Request $request): View|Application|Factory|ApplicationContract
    {
        return view('auth/silent-callback');
    }

    public function check(Request $request): Application|RedirectorAlias|RedirectResponse|ApplicationContract
    {
        $requestId = $request->get('request_id');
        $code = $request->get('code');

        $verified = $this->vonageClient->verify2()->check($requestId, $code);

        if ($verified) {
            $email = $request->session()->get('email');
            $user = User::where('email', $email)->first();
            $user->last_login = Carbon::now();
            $user->save();

            Auth::login($user);

            Session::forget('request_id');
            Session::forget('phone_number');
            Session::forget('email');

            return redirect(route('dashboard'));
        } else {
            $request->session()->flash('error', 'There was an error authenticating.');
            return redirect(route('login'));
        }
    }
}
