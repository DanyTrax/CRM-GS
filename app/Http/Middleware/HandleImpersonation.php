<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('impersonating')) {
            $impersonatedId = $request->session()->get('impersonating');
            $user = \App\Models\User::find($impersonatedId);
            
            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
