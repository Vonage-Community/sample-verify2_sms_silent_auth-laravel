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
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Vonage\Client;
use Vonage\Verify2\Request\SilentAuthRequest;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

class SilentController extends Controller
{
    private Client $vonageClient;

    public function __construct()
    {
        $credentials = new Client\Credentials\Keypair(
            file_get_contents(base_path(config('vonage.privateKey'))),
            config('vonage.applicationId')
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

        $fallbackWorkflow = new VerificationWorkflow(
            VerificationWorkflow::WORKFLOW_SMS,
            $phoneNumber
        );

        $twoFactorRequest->addWorkflow($fallbackWorkflow);

        try {
            $response = $this->vonageClient->verify2()->startVerification($twoFactorRequest);
        } catch (\Exception $e) {
            if ($e->getCode() === 409) {
                Log::error('409 from Silent Auth attempt');
                $request->session()->flash('error', 'You have attempted 2FA too many times, please wait');
                return redirect(route('login'));
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
