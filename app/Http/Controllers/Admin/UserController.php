<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        if (in_array($request->query('role'), ['admin', 'agent', 'customer'], true)) {
            $query->where('role', $request->query('role'));
        }
        if (in_array($request->query('active'), ['yes', 'no'], true)) {
            $query->where('is_active', $request->query('active') === 'yes');
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'totals' => User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User, 'editing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:40', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'agent', 'customer'])],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = (bool) $data['is_active'];
        $data['must_change_password'] = $request->boolean('must_change_password') || $data['role'] === 'admin';
        $user = User::create($data);

        Audit::record($request, 'user.created', "Created {$user->role} account {$user->username}.", $user, [
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return redirect()->route('admin.users.edit', $user)->with('status', 'User account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'editing' => true]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:40', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'agent', 'customer'])],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if ($user->is($request->user()) && ($data['role'] !== 'admin' || ! (bool) $data['is_active'])) {
            return back()->withErrors(['role' => 'You cannot remove administrator access or deactivate your own account.'])->withInput();
        }

        if ($user->role === 'admin' && ($data['role'] !== 'admin' || ! (bool) $data['is_active'])) {
            $otherActiveAdmins = User::query()->where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->exists();
            if (! $otherActiveAdmins) {
                return back()->withErrors(['role' => 'The last active administrator cannot be demoted or deactivated.'])->withInput();
            }
        }

        $before = $user->only(['name', 'username', 'email', 'role', 'is_active']);
        $wasAdmin = $user->isAdmin();
        $user->fill(collect($data)->except(['password'])->all());
        if (! $wasAdmin && $user->isAdmin()) {
            $user->must_change_password = true;
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->must_change_password = true;
        }
        $user->save();

        Audit::record($request, 'user.updated', "Updated account {$user->username}.", $user, [
            'before' => $before,
            'after' => $user->only(['name', 'username', 'email', 'role', 'is_active']),
        ]);

        return redirect()->route('admin.users.edit', $user)->with('status', 'User account updated.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403, 'You cannot deactivate your own account.');
        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::query()->where('role', 'admin')->where('is_active', true)->where('id', '!=', $user->id)->exists();
            abort_unless($otherActiveAdmins, 422, 'The last active administrator cannot be deactivated.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();
        $state = $user->is_active ? 'activated' : 'deactivated';
        Audit::record($request, "user.{$state}", "User {$user->username} was {$state}.", $user);

        return back()->with('status', "User account {$state}.");
    }
}
