<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Responses\ApiResponse;
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        $userRole = $user->role;
        if (!in_array($userRole, $roles)) {
            return ApiResponse::error('Forbidden', 403);
        }
        
        return $next($request);
    }
}

