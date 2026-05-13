<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string   $type,      // scheduled | approved | delivered
        public readonly Delivery $delivery,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /* ── Database payload ── */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => $this->type,
            'delivery_id' => $this->delivery->id,
            'order_number'=> $this->delivery->order->order_number ?? null,
            'message'     => $this->message(),
            'url'         => route('deliveries.show', $this->delivery),
        ];
    }

    /* ── Email ── */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Country Yoghurt] ' . $this->emailSubject())
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line($this->message())
            ->action('View Delivery', route('deliveries.show', $this->delivery))
            ->line('Thank you for using Country Yoghurt Inventory.');
    }

    /* ── Helpers ── */
    private function message(): string
    {
        $num = $this->delivery->order->order_number ?? '#' . $this->delivery->id;

        return match ($this->type) {
            'scheduled'  => "A delivery for order {$num} has been scheduled by {$this->delivery->staff?->name} and awaits your approval.",
            'approved'   => "Your delivery for order {$num} has been received and is now out for delivery.",
            'delivered'  => "Your order {$num} has been delivered successfully.",
            default      => "Delivery for order {$num} status updated.",
        };
    }

    private function emailSubject(): string
    {
        $num = $this->delivery->order->order_number ?? '#' . $this->delivery->id;

        return match ($this->type) {
            'scheduled'  => "Delivery Scheduled for Order {$num}",
            'approved'   => "Delivery for Order {$num} Approved",
            'delivered'  => "Order {$num} Delivered",
            default      => "Delivery Updated",
        };
    }
}
