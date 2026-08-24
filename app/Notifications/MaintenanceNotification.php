<?php

namespace App\Notifications;

use App\Models\MaintenanceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * $type can be: 'scheduled', 'completed', 'cancelled'
     */
    public function __construct(
        private readonly MaintenanceOrder $order,
        private readonly string $type
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resourceName = $this->order->resource->name;
        $scheduled = $this->order->scheduled_at->format('d M Y H:i');

        $message = (new MailMessage)
            ->subject("FaciliCore Maintenance Update: " . ucfirst($this->type))
            ->greeting("Hello {$notifiable->name},");

        if ($this->type === 'scheduled') {
            $message->line("You have been assigned to carry out maintenance on {$resourceName}.")
                    ->line("Scheduled start time: {$scheduled}")
                    ->action('View Work Order', config('app.frontend_url') . '/maintenance');
        } elseif ($this->type === 'completed') {
            $message->line("Maintenance work order for {$resourceName} has been successfully completed.")
                    ->line("Actual Cost: £" . number_format($this->order->cost ?? 0, 2))
                    ->line("Notes: {$this->order->notes}");
        } elseif ($this->type === 'cancelled') {
            $message->line("Maintenance work order for {$resourceName} scheduled on {$scheduled} has been cancelled.");
        }

        return $message->line('Thank you for your service!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'maintenance_order_id' => $this->order->id,
            'resource_name'        => $this->order->resource->name,
            'scheduled_at'         => $this->order->scheduled_at->toIso8601String(),
            'type'                 => $this->type,
            'cost'                 => $this->order->cost,
            'notes'                => $this->order->notes,
            'message'              => $this->getMessageText(),
        ];
    }

    private function getMessageText(): string
    {
        $resourceName = $this->order->resource->name;
        
        return match ($this->type) {
            'scheduled' => "You have been assigned to maintenance for {$resourceName}.",
            'completed' => "Maintenance on {$resourceName} completed.",
            'cancelled' => "Maintenance order for {$resourceName} cancelled.",
            default     => "Maintenance update for {$resourceName}."
        };
    }
}
