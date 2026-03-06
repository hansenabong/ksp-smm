<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSingleSession
{
    /**
     * Kick out any previously active session for this user.
     *
     * On every authenticated request we compare the session's stored
     * session_token with the value in the database.  If they differ,
     * another browser has logged in and invalidated this session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user  = Auth::user();
            $dbToken = $user->session_token;
            $sessToken = $request->session()->get('session_token');

            if ($dbToken === null || $sessToken !== $dbToken) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Sesi Anda telah berakhir karena akun ini masuk dari perangkat lain.']);
            }
        }

        return $next($request);
    }
}
