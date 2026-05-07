<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Models\User;
use App\Services\BulkSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function __construct(private BulkSmsService $sms) {}

    // ─── History ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $logs = SmsLog::with('sender')
            ->latest()
            ->paginate(20);

        $balance = $this->sms->getBalance();

        return view('admin.sms.index', compact('user', 'logs', 'balance'));
    }

    // ─── Compose ──────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $users = User::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'role']);

        $balance = $this->sms->getBalance();

        return view('admin.sms.create', compact('user', 'users', 'balance'));
    }

    // ─── Send ─────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        if (!$request->user()->isAdmin()) abort(403);
        $request->validate([
            'recipient_type' => 'required|in:all,customers,staff,custom',
            'user_ids'       => 'required_if:recipient_type,custom|array|min:1',
            'user_ids.*'     => 'integer|exists:users,id',
            'message'        => 'required|string|max:918',
        ]);

        $type  = $request->input('recipient_type');
        $users = $this->resolveRecipients($type, $request->input('user_ids', []));

        if ($users->isEmpty()) {
            return back()->withInput()->withErrors([
                'recipient_type' => 'No users with phone numbers found for the selected group.',
            ]);
        }

        // Create log record
        $log = SmsLog::create([
            'sender_id'       => $request->user()->id,
            'recipient_type'  => $type,
            'message'         => $request->input('message'),
            'recipient_count' => $users->count(),
            'sent_count'      => 0,
            'failed_count'    => 0,
            'status'          => 'sending',
        ]);

        $message = $request->input('message');
        $sent    = 0;
        $failed  = 0;

        foreach ($users as $user) {
            $success = $this->sms->send($user->phone, $message);

            $log->recipients()->create([
                'user_id' => $user->id,
                'name'    => $user->name,
                'phone'   => $user->phone,
                'status'  => $success ? 'sent' : 'failed',
            ]);

            $success ? $sent++ : $failed++;
        }

        $status = match (true) {
            $failed === 0        => 'completed',
            $sent   === 0        => 'failed',
            default              => 'partial',
        };

        $log->update([
            'sent_count'   => $sent,
            'failed_count' => $failed,
            'status'       => $status,
        ]);

        return redirect()->route('admin.sms.index')
            ->with('status', "SMS sent to {$sent} of {$users->count()} recipients.");
    }

    // ─── Detail ───────────────────────────────────────────────────────────────

    public function show(SmsLog $smsLog, Request $request): View
    {
        $user = $request->user();
        if (!$user->isAdmin()) abort(403);

        $smsLog->load(['sender', 'recipients']);

        return view('admin.sms.show', compact('user', 'smsLog'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveRecipients(string $type, array $ids): \Illuminate\Support\Collection
    {
        $base = User::whereNotNull('phone')->where('phone', '!=', '');

        return match ($type) {
            'all'       => $base->get(['id', 'name', 'phone', 'role']),
            'customers' => $base->where('role', 'customer')->get(['id', 'name', 'phone', 'role']),
            'staff'     => $base->whereIn('role', ['staff', 'admin', 'super_admin'])->get(['id', 'name', 'phone', 'role']),
            'custom'    => $base->whereIn('id', $ids)->get(['id', 'name', 'phone', 'role']),
            default     => collect(),
        };
    }
}
