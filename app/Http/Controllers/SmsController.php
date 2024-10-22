<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Vonage\Client;
use Vonage\Verify2\Request\SMSRequest;

class SmsController extends Controller
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

    public function start(Request $request): Application|View|Factory|Redirector|ApplicationContract|RedirectResponse
    {
        $requestId = $request->session()->get('request_id');
        Log::info('Got request ID ' . $requestId);

        if (!$requestId) {
            Log::error('Did not find a request ID');
            $request->session()->flash('error', 'You have attempted 2FA too many times, please wait');
            return redirect(route('login'));
        }

        try {
            $this->vonageClient->verify2()->nextWorkflow($requestId);
        } catch (Client\Exception\Request $e) {
            Log::error($e->getMessage());
            if ($e->getCode() === 409) {
                Log::error('409, flash error');
                $request->session()->flash('error', 'You have attempted 2FA too many times, please wait');
                return redirect(route('login'));
            }
        }

        return view('auth/sms');
    }

    public function check(Request $request)
    {
        $code = $request->get('code');
        $requestId = $request->session()->get('request_id');

        try {
            $result = $this->vonageClient->verify2()->check($requestId, $code);
        } catch (Client\Exception\Request $e) {
            $request->session()->flash('error', 'Code Error');
            return redirect(route('login'));
        }

        if ($result) {
            $email = $request->session()->get('email');
            $user = User::where('email', $email)->first();
            $user->last_login = Carbon::now();
            $user->save();

            Auth::login($user);

            $request->session()->forget('request_id');
            $request->session()->forget('phone_number');
            $request->session()->forget('email');

            return redirect(route('dashboard'));
        }

        $request->session()->flash('error', 'Authentication Error');
        return redirect(route('login'));
    }
}
