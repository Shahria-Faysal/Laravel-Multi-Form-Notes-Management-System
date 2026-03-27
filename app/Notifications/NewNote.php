<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

class NewNote extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $note;

    public function __construct($note)
    {
        $this->note = $note;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Note Added')
            ->line('A new note has been created.')
            ->line('Title: ' . $this->note->title)
            ->action('View Notes', url('/notes'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'New note: ' . $this->note->title.' has been added',
            'note_id' => $this->note->id,
        ]);
    }

    public function toDatabase($notifiable)
    {
        // dd('toDatabase called');
        return [
            'note_id' => $this->note->id,
            'message' => 'Your Note has been added',
            'title' => $this->note->title,
        ];
    }



    // public function toArray($notifiable): array
    // {
    //     return [
    //         'type' => 'note',
    //         'title' => 'New note added',
    //         'message' => 'Note "' . $this->note->title . '" was created.',
    //         'note_id' => $this->note->id,
    //     ];
    // }
}
