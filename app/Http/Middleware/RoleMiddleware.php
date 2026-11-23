<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use app\Traits\ApiResponse;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    use ApiResponse;
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
         $user = auth('api')->user();

        if (!$user) {
            return $this->error('Token tidak valid atau tidak ada', 401);
        }
        else if (!in_array($user->role, $roles, true)) {
            return $this->error('Anda tidak memiliki akses ke sumber daya ini', 403);
        }
        
        return $next($request);
    }
}
