<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = config('services.admin_gate.username');
        $expectedPass = config('services.admin_gate.password');

        $user = $request->getUser();
        $pass = $request->getPassword();

        $valid = $expectedUser && $expectedPass
            && hash_equals($expectedUser, (string) $user)
            && hash_equals($expectedPass, (string) $pass);

        if (! $valid) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="Admin"']);
        }

        return $next($request);
    }
}
