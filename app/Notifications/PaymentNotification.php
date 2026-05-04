<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string  $type,    // submitted | approved | rejected
        public readonly Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /* ── Database payload ── */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => $this->type,
            'payment_id' => $this->payment->id,
            'amount'     => $this->payment->amount,
            'message'    => $this->message(),
            'url'        => route('payments.show', $this->payment),
        ];
    }

    /* ── Email ── */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Country Yoghurt] ' . $this->emailSubject())
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line($this->message())
            ->action('View Payment', route('payments.show', $this->payment))
            ->line('Thank you for using Country Yoghurt Inventory.');
    }

    /* ── Helpers ── */
    private function message(): string
    {
        $amount = '₦' . number_format($this->payment->amount, 2);
        $ref    = $this->payment->order
            ? "for order {$this->payment->order->order_number}"
            : ($this->payment->reason ? "({$this->payment->reason})" : '');

        return match ($this->type) {
            'submitted' => "A new payment of {$amount} {$ref} has been submitted and awaits review.",
            'approved'  => "Your payment of {$amount} {$ref} has been approved.",
            'rejected'  => "Your payment of {$amount} {$ref} has been rejected." .
                           ($this->payment->rejection_reason ? " Reason: {$this->payment->rejection_reason}" : ''),
            default     => "Payment of {$amount} status updated.",
        };
    }

    private function emailSubject(): string
    {
        $amount = '₦' . number_format($this->payment->amount, 2);

        return match ($this->type) {
            'submitted' => "New Payment of {$amount} Submitted",
            'approved'  => "Payment of {$amount} Approved",
            'rejected'  => "Payment of {$amount} Rejected",
            default     => "Payment Updated",
        };
    }
}
