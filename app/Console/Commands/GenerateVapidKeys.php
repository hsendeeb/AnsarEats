<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;
use Illuminate\Support\Facades\File;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';
    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle()
    {
        $keys = VAPID::createVapidKeys();

        $path = base_path('.env');
        $env = File::get($path);

        $keysToAdd = [
            'VAPID_PUBLIC_KEY' => $keys['publicKey'],
            'VAPID_PRIVATE_KEY' => $keys['privateKey'],
            'VAPID_SUBJECT' => 'mailto:admin@ansareats.com',
        ];

        foreach ($keysToAdd as $key => $value) {
            if (str_contains($env, $key . '=')) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }

        File::put($path, $env);
        
        $this->info('VAPID keys generated successfully and added to .env!');
        $this->info("Public Key: {$keys['publicKey']}");
    }
}
