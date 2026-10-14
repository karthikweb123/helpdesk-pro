<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:40', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $before = $user->only(['name', 'username', 'email']);
        $user->update($data);

        Audit::record($request, 'admin.profile.updated', 'Updated administrator profile.', $user, [
            'before' => $before,
            'after' => $user->only(['name', 'username', 'email']),
        ]);

        return back()->with('status', 'Profile updated.');
    }

    public function passwordForm(Request $request): View
    {
        abort_unless($request->user()->is_active, 403);

        return view('auth.password-change', ['forced' => $request->user()->must_change_password]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        abort_unless($request->user()->is_active, 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;
        $user->setRememberToken(str()->random(60));
        $user->save();
        $request->session()->regenerate();

        Audit::record($request, 'password.changed', "{$user->roleLabel()} {$user->username} changed their password.", $user);

        return redirect()->route('dashboard')->with('status', 'Password changed successfully.');
    }
}
