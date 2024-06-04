<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class VonageSilentAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = Auth::user();

        // if there is no user, we give back the error response
        if (!$user) {
            return $response;
        }

        // is this a super user?
        if ($user->role === 'super-user') {
            return $response;
        }

        // is this the first time logging in?
        if (empty($user->last_login)) {
            $request->session()->put('email', $user->email);
            $request->session()->put('phone_number', $user->phone_number);
            Auth::logout();

            return redirect()->route('silent');
        }

        // was the last login over 4 days ago
        $lastLogin = Carbon::make($user->last_login);

        if ($lastLogin->diffInDays(Carbon::now()) > 4) {
            $request->session()->put('email', $user->email);
            $request->session()->put('phone_number', $user->phone_number);
            Auth::logout();

            return redirect()->route('silent');
        }

        // Update and passthrough
        $user->last_login = Carbon::now();
        $user->save();

        return $response;
    }
}
