<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $user     = $request->user();
        if (!$user->isAdmin()) abort(403);

        $accounts = BankAccount::with('staff')->get();
        $staff    = User::where('role', 'staff')->orderBy('name')->get(['id', 'name', 'state', 'staff_states']);

        return view('admin.bank_accounts.index', compact('user', 'accounts', 'staff'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $data = $request->validate([
            'staff_id'       => 'required|integer|exists:users,id',
            'bank_name'      => 'required|string|max:150',
            'account_name'   => 'required|string|max:200',
            'account_number' => 'required|string|max:20',
        ]);

        $staffMember = User::where('role', 'staff')->findOrFail($data['staff_id']);

        BankAccount::updateOrCreate(
            ['staff_id' => $data['staff_id']],
            array_merge($data, ['created_by' => $user->id])
        );

        return back()->with('status', "Bank details for {$staffMember->name} saved.");
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $name = optional($bankAccount->staff)->name ?? 'this staff';
        $bankAccount->delete();

        return back()->with('status', "Bank details for {$name} removed.");
    }

    public function customerView(Request $request)
    {
        $user = $request->user();

        // Find the staff member who covers this customer's state (and LGA if set)
        $staffMember = User::where('role', 'staff')
            ->whereJsonContains('staff_states', $user->state)
            ->where(function ($q) use ($user) {
                $q->whereNull('staff_lgas')
                  ->orWhereRaw('JSON_LENGTH(staff_lgas) = 0');
                if ($user->lga) {
                    $q->orWhereJsonContains('staff_lgas', $user->lga);
                }
            })
            ->first();

        $account = $staffMember ? BankAccount::where('staff_id', $staffMember->id)->first() : null;

        return view('bank_accounts.show', compact('user', 'account', 'staffMember'));
    }
}
