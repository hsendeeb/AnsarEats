<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\BroadcastNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendBulkNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $targetRole = null,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $query = User::query();

        if ($this->targetRole) {
            $query->where('role', $this->targetRole);
        }

        $query->chunkById(500, function ($users) {
            $now = now();
            $notifications = $users->map(function ($user) use ($now) {
                return [
                    'id' => (string) Str::uuid(),
                    'type' => BroadcastNotification::class,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $this->title,
                        'body' => $this->body,
                    ]),
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            DB::table('notifications')->insert($notifications);
        });
    }
}
