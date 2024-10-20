<?php

namespace App\Notifications;

use App\Mail\AppMail;
use App\Mail\TestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification implements ShouldQueue
// class AppNotification extends Notification 
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $title, public string $content)
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels= ["database"];

        if($notifiable->isEmailVerified()){
            array_push($channels,"mail");
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): AppMail
    {
        return new AppMail(
            $notifiable->email,
            $this->title,
            $this->content
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {

        $data=[
            "title"=>$this->title,
            "content"=>$this->content
        ];

        return $data;
    }
    

}
