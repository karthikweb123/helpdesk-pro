<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Ticket $ticket,
        private readonly string $update,
        private readonly string $details,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket update: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->update)
            ->line($this->details)
            ->action('View ticket', route('tickets.show', $this->ticket))
            ->line('Thank you for contacting support.');
    }
}
