<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ColetaStatusNotification extends Notification
{
    use Queueable;

    protected $mensagem;
    protected $coleta_id;
    protected $tipo;

    /**
     * Create a new notification instance.
     */
    public function __construct($mensagem, $coleta_id, $tipo)
    {
        $this->mensagem = $mensagem;
        $this->coleta_id = $coleta_id;
        $this->tipo = $tipo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'mensagem' => $this->mensagem,
            'coleta_id' => $this->coleta_id,
            'tipo' => $this->tipo,
        ];
    }
}
