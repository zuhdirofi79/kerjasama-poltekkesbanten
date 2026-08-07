<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->orderByRaw("CASE role WHEN 'superadmin1' THEN 1 WHEN 'superadmin2' THEN 2 WHEN 'admin' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = collect(UserRole::cases())->filter(fn (UserRole $r) => $r !== UserRole::Superadmin1);

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in([
                UserRole::Superadmin2->value,
                UserRole::Admin->value,
                UserRole::Visitor->value,
            ])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $this->auditLogService->log('user.created', $user);

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = collect(UserRole::cases())->filter(fn (UserRole $r) => $r !== UserRole::Superadmin1);

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($request->user()->can('changeRole', $user)) {
            $rules['role'] = ['required', Rule::in([
                UserRole::Superadmin2->value,
                UserRole::Admin->value,
                UserRole::Visitor->value,
            ])];
        }

        $validated = $request->validate($rules);

        $old = $user->only(['name', 'email', 'role', 'is_active']);

        $payload = [
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
        ];

        if ($request->user()->can('changeRole', $user) && isset($validated['role'])) {
            $payload['role'] = $validated['role'];
        }

        if ($request->user()->can('deactivate', $user) || $request->user()->id === $user->id) {
            if ($request->has('is_active') && $request->user()->can('deactivate', $user)) {
                $payload['is_active'] = $request->boolean('is_active');
            }
        }

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        $this->auditLogService->log('user.updated', $user, $old, $user->only(['name', 'email', 'role', 'is_active']));

        return redirect()->route('admin.users.index')->with('status', 'Pengguna diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();
        $this->auditLogService->log('user.soft_deleted', $user);

        return redirect()->route('admin.users.index')->with('status', 'Pengguna diarsipkan.');
    }
}
