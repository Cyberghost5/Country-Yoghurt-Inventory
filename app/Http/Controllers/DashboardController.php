<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stateContacts = collect();
        $adminStats = null;
        $recentStaff = collect();
        $recentCustomers = collect();

        if (in_array($user->role, ['staff', 'customer'], true) && $user->state) {
            $stateContacts = User::query()
                ->where('state', $user->state)
                ->whereIn('role', ['staff', 'customer'])
                ->where('id', '!=', $user->id)
                ->orderBy('role')
                ->orderBy('name')
                ->get(['name', 'role', 'phone', 'state', 'lga', 'shop_name']);
        }

        if ($user->role === 'admin') {
            $adminStats = [
                'staffCount' => User::where('role', 'staff')->count(),
                'customerCount' => User::where('role', 'customer')->count(),
                'stateCount' => User::whereNotNull('state')->distinct('state')->count('state'),
            ];

            $recentStaff = User::where('role', 'staff')
                ->latest()
                ->limit(5)
                ->get(['name', 'email', 'state', 'lga', 'created_at']);

            $recentCustomers = User::where('role', 'customer')
                ->latest()
                ->limit(5)
                ->get(['name', 'shop_name', 'state', 'lga', 'created_at']);
        }

        return view('dashboard', compact('user', 'stateContacts', 'adminStats', 'recentStaff', 'recentCustomers'));
    }
}
