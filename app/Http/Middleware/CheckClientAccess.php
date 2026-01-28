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
        
        // Si el usuario tiene rol de cliente, verificar acceso a sus propios datos
        // Si es admin, permitir acceso a todo
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('Cliente')) {
            $client = $user->client;
            
            if (!$client) {
                abort(403, 'No tiene un cliente asociado');
            }

            // Verificar acceso a recursos del cliente
            $routeParams = $request->route()?->parameters() ?? [];
            
            if (isset($routeParams['client'])) {
                if ($routeParams['client']->id !== $client->id) {
                    abort(403);
                }
            }
        }

        // Si es admin o tiene otros roles, permitir acceso
        return $next($request);
    }
}
