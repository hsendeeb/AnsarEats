<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizePhones('users', $this->conflictingNormalizedUserPhones());
        $this->normalizePhones('orders');
    }

    public function down(): void
    {
        //
    }

    private function normalizePhones(string $table, array $skipNormalizedPhones = []): void
    {
        DB::table($table)
            ->whereNotNull('phone')
            ->orderBy('id')
            ->select(['id', 'phone'])
            ->chunkById(100, function ($rows) use ($table, $skipNormalizedPhones) {
                foreach ($rows as $row) {
                    $normalized = preg_replace('/\s+/', '', (string) $row->phone);

                    if (in_array($normalized, $skipNormalizedPhones, true)) {
                        continue;
                    }

                    if ($normalized !== $row->phone) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['phone' => $normalized]);
                    }
                }
            });
    }

    private function conflictingNormalizedUserPhones(): array
    {
        return DB::table('users')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select(['id', 'phone'])
            ->get()
            ->groupBy(fn ($user) => preg_replace('/\s+/', '', (string) $user->phone))
            ->filter(fn ($users) => $users->count() > 1)
            ->keys()
            ->map(fn ($phone) => (string) $phone)
            ->all();
    }
};
