<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $type,   // placed | approved | rejected | delivered
        public readonly Order  $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /* ── Database payload ── */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => $this->type,
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'message'      => $this->message(),
            'url'          => route('orders.show', $this->order),
        ];
    }

    /* ── Email ── */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Country Yoghurt] ' . $this->emailSubject())
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line($this->message())
            ->action('View Order', route('orders.show', $this->order))
            ->line('Thank you for using Country Yoghurt Inventory.');
    }

    /* ── Helpers ── */
    private function message(): string
    {
        $num    = $this->order->order_number;
        $amount = '₦' . number_format($this->order->total_amount, 2);

        return match ($this->type) {
            'placed'    => "A new order {$num} ({$amount}) has been placed and is awaiting your approval.",
            'approved'  => "Your order {$num} ({$amount}) has been approved.",
            'rejected'  => "Your order {$num} has been rejected.",
            'delivered' => "Your order {$num} has been marked as delivered.",
            default     => "Order {$num} status updated.",
        };
    }

    private function emailSubject(): string
    {
        $num = $this->order->order_number;

        return match ($this->type) {
            'placed'    => "New Order {$num} Placed",
            'approved'  => "Order {$num} Approved",
            'rejected'  => "Order {$num} Rejected",
            'delivered' => "Order {$num} Delivered",
            default     => "Order {$num} Updated",
        };
    }
}
