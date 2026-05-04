<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminPagesController extends Controller
{
    public function staffIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $staff = User::where('role', 'staff')
            ->orderBy('state')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'state', 'lga', 'created_at']);

        return view('admin.staff-index', compact('user', 'staff'));
    }

    public function adminIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $admins = User::where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'state', 'lga', 'created_at']);

        return view('admin.admin-index', compact('user', 'admins'));
    }

    public function customerIndex(Request $request)
    {
        $this->ensureAdmin($request);

        $user = $request->user();
        $customers = User::where('role', 'customer')
            ->orderBy('state')
            ->orderBy('shop_name')
            ->get(['id', 'name', 'shop_name', 'email', 'phone', 'address', 'state', 'lga', 'created_at']);

        return view('admin.customer-index', compact('user', 'customers'));
    }

    private function ensureAdmin(Request $request): void
    {
        if (($request->user()->role ?? null) !== 'admin') {
            abort(403);
        }
    }
}
