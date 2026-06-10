<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $title,
        public string $body,
        public string $url,
        public ?string $targetRole = null,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $query = User::whereHas('pushSubscriptions');

        if ($this->targetRole) {
            $query->where('role', $this->targetRole);
        }

        $query->chunkById(200, function ($users) {
            foreach ($users as $user) {
                SendPushNotification::dispatch(
                    $user->id,
                    [
                        'title' => $this->title,
                        'body' => $this->body,
                        'url' => $this->url,
                        'audience' => $this->targetRole,
                    ]
                );
            }
        });
    }
}
