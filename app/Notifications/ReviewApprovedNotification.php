<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Review $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
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
            ->subject('Tu reseña ha sido aprobada - Pacha Tour')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('¡Excelentes noticias! Tu reseña ha sido aprobada y ya está visible para otros viajeros.')
            ->line('**Reseña:** ' . $this->review->title)
            ->line('**Calificación:** ' . $this->review->rating . '/5 estrellas')
            ->line('Gracias por compartir tu experiencia y ayudar a otros viajeros a descubrir Bolivia.')
            ->action('Ver mi reseña', url('/attractions/' . $this->review->reviewable->slug . '#review-' . $this->review->id))
            ->line('¡Esperamos verte pronto en tu próxima aventura!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_approved',
            'review_id' => $this->review->id,
            'review_title' => $this->review->title,
            'rating' => $this->review->rating,
            'reviewable_type' => class_basename($this->review->reviewable_type),
            'reviewable_name' => $this->review->reviewable->name ?? 'N/A',
        ];
    }
}