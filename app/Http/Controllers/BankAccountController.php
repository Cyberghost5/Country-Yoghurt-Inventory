<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $accounts = BankAccount::orderBy('state')->get();
        $states   = array_keys(config('nigeria.lgas'));
        sort($states);

        return view('admin.bank_accounts.index', compact('user', 'accounts', 'states'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $data = $request->validate([
            'state'          => 'required|string|max:100',
            'bank_name'      => 'required|string|max:150',
            'account_name'   => 'required|string|max:200',
            'account_number' => 'required|string|max:20',
        ]);

        BankAccount::updateOrCreate(
            ['state' => $data['state']],
            array_merge($data, ['created_by' => $user->id])
        );

        return back()->with('status', "Bank details for {$data['state']} saved.");
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $state = $bankAccount->state;
        $bankAccount->delete();

        return back()->with('status', "Bank details for {$state} removed.");
    }

    public function customerView(Request $request)
    {
        $user    = $request->user();
        $account = BankAccount::where('state', $user->state)->first();
        $all     = BankAccount::orderBy('state')->get();

        return view('bank_accounts.show', compact('user', 'account', 'all'));
    }
}
