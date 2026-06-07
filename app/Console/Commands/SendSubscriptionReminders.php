<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Send email reminders to restaurant owners whose subscription expires within 2 days';

    public function handle(): int
    {
        $restaurants = Restaurant::with('user')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>', now())
            ->where('subscription_ends_at', '<=', now()->addDays(2))
            ->whereNull('subscription_reminder_sent_at')
            ->get();

        if ($restaurants->isEmpty()) {
            $this->info('No subscription reminders to send.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($restaurants as $restaurant) {
            $owner = $restaurant->user;

            if (! $owner || ! $owner->email) {
                continue;
            }

            Mail::to($owner->email)->queue(
                new SubscriptionExpiringMail($owner, $restaurant)
            );

            $restaurant->update(['subscription_reminder_sent_at' => now()]);

            $sent++;
        }

        $this->info("Sent {$sent} subscription reminder(s).");

        return self::SUCCESS;
    }
}
