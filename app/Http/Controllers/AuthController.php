<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\RegistrationVerificationCode;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const VERIFICATION_CODE_TTL_MINUTES = 10;

    private const MAX_VERIFICATION_ATTEMPTS = 5;

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $credentials['login'];
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([
            $field => $identifier,
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'These credentials do not match an active account.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        if ($request->user()->isAdmin()) {
            Audit::record($request, 'admin.login', "Administrator {$request->user()->username} signed in.", $request->user());
        }

        if ($request->user()->must_change_password) {
            return redirect()->route('account.password.edit');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function registerForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        if ($request->boolean('resend_code')) {
            $email = $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ])['email'];

            return $this->sendRegistrationVerificationCode($request, $email);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:40', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! $request->filled('verification_code')) {
            return $this->sendRegistrationVerificationCode($request, $data['email']);
        }

        $code = $request->input('verification_code');
        if (! is_string($code) || ! preg_match('/\A[0-9]{6}\z/', $code)) {
            return $this->registrationVerificationError(
                $request,
                'Enter the six-digit verification code sent to your email address.'
            );
        }

        $verificationKey = $this->registrationVerificationCacheKey($data['email']);
        $verification = Cache::get($verificationKey);
        if (
            ! is_array($verification)
            || ! isset($verification['hash'], $verification['attempts'])
            || ! is_string($verification['hash'])
            || ! Hash::check($code, $verification['hash'])
        ) {
            if (
                is_array($verification)
                && isset($verification['hash'], $verification['attempts'])
                && is_string($verification['hash'])
            ) {
                $attempts = (int) $verification['attempts'] + 1;

                if ($attempts >= self::MAX_VERIFICATION_ATTEMPTS) {
                    Cache::forget($verificationKey);
                } else {
                    Cache::put($verificationKey, [
                        'hash' => $verification['hash'],
                        'attempts' => $attempts,
                    ], now()->addMinutes(self::VERIFICATION_CODE_TTL_MINUTES));
                }
            }

            return $this->registrationVerificationError(
                $request,
                'That verification code is invalid or expired. Request a new code and try again.'
            );
        }

        Cache::forget($verificationKey);

        $user = new User([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    private function sendRegistrationVerificationCode(Request $request, string $email): RedirectResponse
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->registrationVerificationCacheKey($email), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::VERIFICATION_CODE_TTL_MINUTES));

        Notification::route('mail', $email)->notify(new RegistrationVerificationCode($code));

        return back()
            ->with('verification_sent', true)
            ->with('status', 'A verification code was sent to your email address.')
            ->withInput($request->except([
                '_token',
                'password',
                'password_confirmation',
                'verification_code',
                'resend_code',
            ]));
    }

    private function registrationVerificationError(Request $request, string $message): RedirectResponse
    {
        return back()
            ->withErrors(['verification_code' => $message])
            ->with('verification_sent', true)
            ->withInput($request->except([
                '_token',
                'password',
                'password_confirmation',
                'verification_code',
                'resend_code',
            ]));
    }

    private function registrationVerificationCacheKey(string $email): string
    {
        return 'registration-email-otp:'.hash('sha256', strtolower($email));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user?->isAdmin()) {
            Audit::record($request, 'admin.logout', "Administrator {$user->username} signed out.", $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
