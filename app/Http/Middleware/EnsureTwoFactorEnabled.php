<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorEnabled
{
  /**
   * Handle an incoming request.
   * If the authenticated user does not have 2FA enabled, redirect them to the 2FA setup page.
   * Exempt a small set of routes (login, logout, register, 2fa routes, assets) to avoid loops.
   */
  public function handle(Request $request, Closure $next)
  {
    // Only enforce for authenticated users
    $user = Auth::user();
    if (!$user) {
      return $next($request);
    }

    // Do not enforce for AJAX/XHR requests (API clients) — they should handle auth separately
    if ($request->ajax() || $request->wantsJson()) {
      return $next($request);
    }

    // Exempt URIs / route names to avoid redirect loops
    $exemptRoutes = [
      'login',
      'logout',
      'register',
      'password.request',
      'password.email',
      'password.reset',
      'password.update',
      '2fa.setup',
      '2fa.enable',
      '2fa.verify',
      '2fa.verify.post',
    ];

    $currentRouteName = optional($request->route())->getName();
    if (in_array($currentRouteName, $exemptRoutes, true)) {
      return $next($request);
    }

    // Also allow the setup/verify URIs in case route names are not set
    $exemptPaths = [
      '2fa/setup',
      '2fa/enable',
      '2fa/verify',
    ];
    foreach ($exemptPaths as $path) {
      if ($request->is(ltrim($path, '/'))) {
        return $next($request);
      }
    }

    // If 2FA is not enabled, redirect to setup
    if (empty($user->google2fa_enabled) || $user->google2fa_enabled == false) {
      return redirect()->route('2fa.setup');
    }

    return $next($request);
  }

}
