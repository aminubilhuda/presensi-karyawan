<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupportTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tiket Dukungan Baru: ' . $this->ticket->ticket_id)
            ->greeting('Halo Admin')
            ->line('Ada tiket dukungan baru dari ' . $this->ticket->user->name . '.')
            ->line('Subjek: ' . $this->ticket->subject)
            ->line('Prioritas: ' . ucfirst($this->ticket->priority))
            ->action('Lihat Tiket', url('/admin/support/' . $this->ticket->id))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_no' => $this->ticket->ticket_id,
            'subject' => $this->ticket->subject,
            'priority' => $this->ticket->priority,
            'user_id' => $this->ticket->user_id,
            'user_name' => $this->ticket->user->name,
        ];
    }
}
