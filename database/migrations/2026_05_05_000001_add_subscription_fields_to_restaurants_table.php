<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->timestamp('subscription_ends_at')->nullable()->after('is_open');
            $table->timestamp('subscription_reminder_sent_at')->nullable()->after('subscription_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['subscription_ends_at', 'subscription_reminder_sent_at']);
        });
    }
};
