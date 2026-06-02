<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $url = $frontendUrl . "/reset-password?token={$token}&email=" . urlencode($notifiable->getEmailForPasswordReset());

            return (new MailMessage)
                ->subject('Notificação de Redefinição de Senha')
                ->greeting('Olá!')
                ->line('Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
                ->action('Redefinir Senha', $url)
                ->line('Este link de redefinição de senha expirará em 30 minutos.')
                ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.')
                ->salutation("Atenciosamente,\n" . config('app.name', 'Ecocerâmica'));
        });
    }
}
