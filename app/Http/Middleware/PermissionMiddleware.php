<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Split permissions by | (pipe)
        $permissions = explode('|', $permission);

        // Check if user has any of the permissions
        foreach ($permissions as $perm) {
            if ($user->can($perm)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
