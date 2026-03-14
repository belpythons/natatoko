<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pin' => ['nullable', 'string', 'size:4'],
            'email' => ['required_without:pin', 'nullable', 'string', 'email'],
            'password' => ['required_without:pin', 'nullable', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // PIN-based authentication (employees)
        if ($this->filled('pin')) {
            $hashedPin = hash('sha256', $this->pin);

            $user = User::where('pin', $hashedPin)
                ->where('role', 'employee')
                ->first();

            if (!$user) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'pin' => __('PIN tidak valid atau bukan akun karyawan.'),
                ]);
            }

            Auth::login($user, false);
            RateLimiter::clear($this->throttleKey());

            return;
        }

        // Email/Password authentication (admin or any role)
        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $field = $this->filled('pin') ? 'pin' : 'email';

        throw ValidationException::withMessages([
            $field => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        if ($this->filled('pin')) {
            return 'pin|' . $this->ip();
        }

        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}