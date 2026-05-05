<?php

namespace App\Mail;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Restaurant $restaurant;
    public string $expiresAt;

    public function __construct(User $user, Restaurant $restaurant)
    {
        $this->user = $user;
        $this->restaurant = $restaurant;
        $this->expiresAt = $restaurant->subscription_ends_at->format('F j, Y');
        $this->onQueue('mail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ' . config('app.name') . ' Subscription Expires Soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription.expiring',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
