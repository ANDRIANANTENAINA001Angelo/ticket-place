<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  string  $token
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL',"http://localhost:8000");  // URL de votre frontend configurée dans le fichier .env
        $resetUrl = "{$frontendUrl}/reset-password/{$token}?email={$notifiable->email}";

        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }

    
}

