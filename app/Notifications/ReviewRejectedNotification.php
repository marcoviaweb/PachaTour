<?php

namespace App\Notifications;

use App\Features\Reviews\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Review $review;
    protected string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review, string $reason)
    {
        $this->review = $review;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
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
            ->subject('Tu reseña ha sido rechazada - Pacha Tour')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Lamentamos informarte que tu reseña ha sido rechazada por nuestro equipo de moderación.')
            ->line('**Reseña:** ' . $this->review->title)
            ->line('**Motivo del rechazo:** ' . $this->reason)
            ->line('Si tienes alguna pregunta sobre esta decisión, no dudes en contactarnos.')
            ->action('Ver mis reseñas', url('/dashboard/reviews'))
            ->line('Gracias por tu comprensión.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_rejected',
            'review_id' => $this->review->id,
            'review_title' => $this->review->title,
            'reason' => $this->reason,
            'reviewable_type' => class_basename($this->review->reviewable_type),
            'reviewable_name' => $this->review->reviewable->name ?? 'N/A',
        ];
    }
}