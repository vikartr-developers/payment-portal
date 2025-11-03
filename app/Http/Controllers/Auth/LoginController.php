<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'app/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * After user is authenticated using password, check if user has 2FA enabled.
     * If enabled, store user id in session and log them out so they must complete TOTP verification.
     */
    public function authenticated(Request $request, $user)
    {
        if (!empty($user->google2fa_enabled) && $user->google2fa_enabled) {
            // Keep the user id to verify TOTP, but logout so they cannot access protected routes
            $request->session()->put('2fa:user:id', $user->id);
            Auth::logout();
            return redirect()->route('2fa.verify');
        }

        return redirect()->intended($this->redirectPath());
    }
}
