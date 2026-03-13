<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        $status = ucfirst($this->order->status);
        return new Envelope(
            subject: "Update: Your order is {$status}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.updated',
        );
    }
}
