<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function createStaff(Request $request)
    {
        if (!$this->canCreateStaff($request->user()->role)) {
            abort(403);
        }

        return view('users.create-staff', [
            'user' => $request->user(),
            'states' => $this->assignableStates($request->user()),
            'lgaMap' => config('nigeria.lgas'),
        ]);
    }

    public function storeStaff(Request $request)
    {
        if (!$this->canCreateStaff($request->user()->role)) {
            abort(403);
        }

        $state = $this->validatedState($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'state' => ['required', 'string'],
            'lga' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->validateLga($state, $data['lga']);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'state' => $state,
            'lga' => $data['lga'],
            'role' => 'staff',
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.create.staff')->with('status', 'Staff account created successfully.');
    }

    public function createCustomer(Request $request)
    {
        if (!$this->canCreateCustomer($request->user()->role)) {
            abort(403);
        }

        return view('users.create-customer', [
            'user' => $request->user(),
            'states' => $this->assignableStates($request->user()),
            'lgaMap' => config('nigeria.lgas'),
        ]);
    }

    public function storeCustomer(Request $request)
    {
        if (!$this->canCreateCustomer($request->user()->role)) {
            abort(403);
        }

        $state = $this->validatedState($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'shop_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string'],
            'lga' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->validateLga($state, $data['lga']);

        User::create([
            'name' => $data['name'],
            'shop_name' => $data['shop_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'state' => $state,
            'lga' => $data['lga'],
            'role' => 'customer',
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.create.customer')->with('status', 'Customer account created successfully.');
    }

    private function canCreateStaff(string $role): bool
    {
        return $role === 'admin';
    }

    public function editUser(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') abort(403);

        return view('users.edit', [
            'user'       => $request->user(),
            'targetUser' => $user,
            'states'     => array_keys(config('nigeria.lgas')),
            'lgaMap'     => config('nigeria.lgas'),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') abort(403);

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['required', 'string', 'max:20'],
            'state'    => ['required', 'string'],
            'lga'      => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($user->role === 'customer') {
            $rules['shop_name'] = ['required', 'string', 'max:255'];
            $rules['address']   = ['required', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        $lgaMap    = config('nigeria.lgas');
        $validLgas = $lgaMap[$data['state']] ?? [];
        if (!in_array($data['lga'], $validLgas, true)) {
            throw ValidationException::withMessages(['lga' => 'Selected LGA is invalid for the selected state.']);
        }

        $updates = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'state' => $data['state'],
            'lga'   => $data['lga'],
        ];

        if ($user->role === 'customer') {
            $updates['shop_name'] = $data['shop_name'];
            $updates['address']   = $data['address'];
        }

        if (!empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        return redirect()->route('users.edit', $user->id)->with('status', 'User updated successfully.');
    }

    public function impersonate(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') abort(403);
        if ($request->user()->id === $user->id) abort(422, 'You cannot impersonate yourself.');
        if (session('impersonating_admin_id')) abort(422, 'Already impersonating a user. Stop first.');

        session(['impersonating_admin_id' => $request->user()->id]);
        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Now impersonating ' . $user->name . '.');
    }

    public function stopImpersonating(Request $request)
    {
        $adminId = session('impersonating_admin_id');
        if (!$adminId) return redirect()->route('dashboard');

        $admin = User::findOrFail($adminId);
        session()->forget('impersonating_admin_id');
        Auth::login($admin);

        return redirect()->route('dashboard')->with('status', 'Returned to your admin account.');
    }

    /* ── AJAX: customers list (state-scoped for staff, all for admin) ── */
    public function ajaxCustomers(Request $request)
    {
        $actor = $request->user();
        if (!in_array($actor->role, ['admin', 'staff'], true)) abort(403);

        $customers = User::where('role', 'customer')
            ->when($actor->role === 'staff', fn ($q) => $q->where('state', $actor->state))
            ->orderBy('name')
            ->get(['id', 'name', 'shop_name', 'state']);

        return response()->json($customers->map(fn ($c) => [
            'id'        => $c->id,
            'name'      => $c->name,
            'shop_name' => $c->shop_name,
            'state'     => $c->state,
        ]));
    }

    public function createAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') abort(403);

        return view('users.create-admin', [
            'user'   => $request->user(),
            'states' => array_keys(config('nigeria.lgas')),
            'lgaMap' => config('nigeria.lgas'),
        ]);
    }

    public function storeAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') abort(403);

        $state = $this->validatedState($request);

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['required', 'string', 'max:20'],
            'state'                 => ['required', 'string'],
            'lga'                   => ['required', 'string', 'max:120'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->validateLga($state, $data['lga']);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'state'    => $state,
            'lga'      => $data['lga'],
            'role'     => 'admin',
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.create.admin')->with('status', 'Admin account created successfully.');
    }

    private function canCreateCustomer(string $role): bool
    {
        return in_array($role, ['admin', 'staff'], true);
    }

    private function assignableStates(User $user): array
    {
        if ($user->role === 'admin') {
            return array_keys(config('nigeria.lgas'));
        }

        return $user->state ? [$user->state] : [];
    }

    private function validatedState(Request $request): string
    {
        $allowedStates = $this->assignableStates($request->user());
        $state = (string) $request->input('state');

        if (!in_array($state, $allowedStates, true)) {
            abort(403, 'You can only create users within your permitted state.');
        }

        return $state;
    }

    private function validateLga(string $state, string $lga): void
    {
        $lgaMap = config('nigeria.lgas');
        $validLgas = $lgaMap[$state] ?? [];

        if (!in_array($lga, $validLgas, true)) {
            throw ValidationException::withMessages([
                'lga' => 'Selected LGA is invalid for the selected state.',
            ]);
        }
    }
}