<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * $type can be: 'confirmed', 'cancelled', 'overridden', 'pending'
     */
    public function __construct(
        private readonly Booking $booking,
        private readonly string $type,
        private readonly ?string $reason = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Database is always enabled; Mail is enabled if the user has an email
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resourceName = $this->booking->resource->name;
        $start = $this->booking->start_at->format('d M Y H:i');
        $end   = $this->booking->end_at->format('H:i');

        $message = (new MailMessage)
            ->subject("FaciliCore Booking Update: " . ucfirst($this->type))
            ->greeting("Hello {$notifiable->name},");

        if ($this->type === 'confirmed') {
            $message->line("Your booking for {$resourceName} on {$start} - {$end} has been confirmed.")
                    ->action('View Booking Details', config('app.frontend_url') . '/bookings');
        } elseif ($this->type === 'pending') {
            $message->line("Your booking request for {$resourceName} on {$start} - {$end} has been submitted and is awaiting approval.");
        } elseif ($this->type === 'cancelled') {
            $message->line("Your booking for {$resourceName} on {$start} - {$end} has been cancelled.")
                    ->line($this->reason ? "Reason: {$this->reason}" : '');
        } elseif ($this->type === 'overridden') {
            $message->line("Your booking for {$resourceName} on {$start} - {$end} was cancelled due to a conflict with a higher priority request.")
                    ->line("You may schedule another slot at your convenience.")
                    ->action('Schedule New Booking', config('app.frontend_url') . '/bookings/create');
        }

        return $message->line('Thank you for using FaciliCore!');
    }

    /**
     * Get the array representation of the notification (saved to database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id'    => $this->booking->id,
            'resource_name' => $this->booking->resource->name,
            'start_at'      => $this->booking->start_at->toIso8601String(),
            'end_at'        => $this->booking->end_at->toIso8601String(),
            'type'          => $this->type,
            'reason'        => $this->reason,
            'message'       => $this->getMessageText(),
        ];
    }

    private function getMessageText(): string
    {
        $resourceName = $this->booking->resource->name;
        $start = $this->booking->start_at->format('d M H:i');

        return match ($this->type) {
            'confirmed'  => "Booking confirmed for {$resourceName} on {$start}.",
            'pending'    => "Booking request for {$resourceName} on {$start} is pending approval.",
            'cancelled'  => "Booking for {$resourceName} on {$start} has been cancelled.",
            'overridden' => "Booking for {$resourceName} on {$start} overridden by higher priority request.",
            default      => "Booking status update for {$resourceName}."
        };
    }
}
