<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $userId,
        public array $payload,
    ) {
    }

    public function handle(PushNotificationService $pushNotificationService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $pushNotificationService->sendToUser($user, $this->payload);
    }
}
