<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Si tiene rol de cliente, verificar que solo acceda a sus propios datos
        if ($user->hasRole('Cliente')) {
            $client = $user->client;
            
            if (!$client) {
                abort(403, 'No tiene un cliente asociado');
            }

            // Verificar acceso a recursos del cliente
            $routeParams = $request->route()->parameters();
            
            if (isset($routeParams['client'])) {
                if ($routeParams['client']->id !== $client->id) {
                    abort(403);
                }
            }
        }

        return $next($request);
    }
}
