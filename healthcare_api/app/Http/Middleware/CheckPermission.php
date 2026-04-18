<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if ($request->user() && $request->user()->isSuperAdmin()) {
            return $next($request);
        }

        if (!$request->user() || !$request->user()->hasPermissionTo($permission)) {
            return response()->json([
                'message' => 'Unauthorized action. Permission required: ' . $permission,
                'required_permission' => $permission
            ], 403);
        }

        return $next($request);
    }
}
