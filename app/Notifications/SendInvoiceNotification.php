<?php

namespace App\Notifications;


use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class SendInvoiceNotification extends Notification
{

    use Queueable;

    protected $user;
    protected $invoice;
    protected $order;
    protected $plan;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $invoice, $order, $plan)
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->order = $order;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data["email"] = "test@gmail.com";
        $data["title"] = "Welcome to NiceSnippets.com";
        $data["body"] = "This is the email body.";
        $pdf = Pdf::loadView('mails.invoice', $data);
        return (new MailMessage)
            ->subject(config('app.name') . '-' . 'FACTURA #' . $this->invoice->id)
            ->attachData($pdf->output(), "test.pdf")
            ->markdown('mails.send-invoice', [
                'user' => $this->user,
                'invoice' => $this->invoice,
                'order' => $this->order,
                'plan' => $this->plan
            ]);

    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'link' => 'link',
            'title' => 'Factura enviada',
            'description' => 'Hemos enviado tu factura para tu suscripción '
        ];
    }
}
