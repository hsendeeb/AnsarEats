<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    public function sendToUser(User $user, array $payload)
    {
        $subscriptions = $user->pushSubscriptions;

        if ($subscriptions->isEmpty()) {
            Log::info('Push: No subscriptions for user #' . $user->id . ' (' . $user->name . ')');
            return;
        }

        Log::info('Push: Found ' . $subscriptions->count() . ' subscription(s) for user #' . $user->id);

        $auth = [
            'VAPID' => [
                'subject' => config('push.vapid.subject', env('VAPID_SUBJECT')),
                'publicKey' => config('push.vapid.public_key', env('VAPID_PUBLIC_KEY')),
                'privateKey' => config('push.vapid.private_key', env('VAPID_PRIVATE_KEY')),
            ],
        ];

        $webPush = new WebPush($auth);

        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->public_key,
                        'auth' => $sub->auth_token,
                    ],
                ]),
                json_encode($payload)
            );
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                Log::info('Push: Delivered to ' . $report->getEndpoint());
            } else {
                Log::warning('Push: Failed to ' . $report->getEndpoint() . ' — ' . $report->getReason());
                if ($report->isSubscriptionExpired()) {
                    \App\Models\PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
                    Log::info('Push: Removed expired subscription');
                }
            }
        }
    }
}
