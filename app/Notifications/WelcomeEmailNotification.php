<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\User;
class WelcomeEmailNotification extends Notification
{
    use Queueable;
    public $details;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct( $user, $details)
    {
        //
        $this->user = $user;
        $this->newpassword = $details['newpassword'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        
                return (new MailMessage)
                ->subject('User Registered at Containerize.Releases Backend')
                ->greeting('Hello, '.$this->user->name)
                ->line('Welcome to Containerize.Releases Backend')
                ->line('Login Credentials ')
                ->line('Email: ' . $this->user->email)
                ->line('Password: ' . $this->newpassword)
                ->action('Explore', url('/'))
                ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
