<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $email = (string) (
            $request->query('email')
            ?: $request->old('email')
            ?: $request->input('email')
            ?: ''
        );
        $resetUser = $email !== ''
            ? User::query()->where('email', $email)->first()
            : null;

        return view('auth.reset-password', [
            'request' => $request,
            'resetUser' => $resetUser,
            'expireMinutes' => (int) config('auth.passwords.users.expire', 60),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('password.updated')->with($this->successPayload($request->email))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }

    public function confirmed(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('password_reset_confirmed')) {
            return redirect()->route('login');
        }

        return view('auth.password-updated', [
            'name' => $request->session()->get('password_reset_name'),
            'email' => $request->session()->get('password_reset_email'),
            'role' => $request->session()->get('password_reset_role'),
        ]);
    }

    /**
     * @return array{password_reset_confirmed: true, password_reset_name: ?string, password_reset_email: string, password_reset_role: ?string}
     */
    private function successPayload(string $email): array
    {
        $user = User::query()->where('email', $email)->first();

        return [
            'password_reset_confirmed' => true,
            'password_reset_name' => $user?->name,
            'password_reset_email' => $user?->email ?? $email,
            'password_reset_role' => $user?->role,
        ];
    }
}
