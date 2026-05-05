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

class SubscriptionExtendedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Restaurant $restaurant;
    public string $validUntil;

    public function __construct(User $user, Restaurant $restaurant)
    {
        $this->user = $user;
        $this->restaurant = $restaurant;
        $this->validUntil = $restaurant->subscription_ends_at->format('F j, Y');
        $this->onQueue('mail');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ' . config('app.name') . ' Subscription Has Been Extended!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription.extended',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
