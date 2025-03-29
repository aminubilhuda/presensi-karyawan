<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketStatusNotification extends Notification implements ShouldQueue
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
        $statusText = match($this->ticket->status) {
            'in_progress' => 'sedang diproses',
            'closed' => 'ditutup',
            default => $this->ticket->status
        };

        return (new MailMessage)
            ->subject('Update Tiket Dukungan: ' . $this->ticket->ticket_id)
            ->greeting('Halo ' . $notifiable->name)
            ->line('Tiket dukungan Anda dengan nomor ' . $this->ticket->ticket_id . ' telah diperbarui.')
            ->line('Status tiket sekarang: ' . $statusText)
            ->action('Lihat Tiket', url('/support/' . $this->ticket->id))
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
            'old_status' => 'updated',
            'new_status' => $this->ticket->status,
        ];
    }
}
