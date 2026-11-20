<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Queued so the forgot-password response takes the same time whether or not an email is actually sent.
final class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablece tu contraseña — IVEA Analítica')
            ->greeting('Hola:')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta del portal IVEA Analítica.')
            ->action('Restablecer contraseña', $this->resetUrl($notifiable))
            ->line("Este enlace vence en {$expireMinutes} minutos.")
            ->line('Si no solicitaste el cambio, ignora este correo; tu contraseña seguirá siendo la misma.')
            ->salutation('Mesa Técnica IVEA');
    }
}
