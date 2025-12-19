<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\DataList;

class SealDailyLogs extends Command
{
    protected $signature = 'security:seal-daily-telemetry {--date=}';

    protected $description = 'Tinh checksum du lieu cam bien theo ngay va ghi vao data_integrity_logs de niem phong.';

    public function handle(): int
    {
        $targetDate = $this->option('date') ?: now()->subDay()->toDateString();
        $secret = env('DB_SEALING_SECRET');

        if (!$secret) {
            $this->error('Chua cau hinh DB_SEALING_SECRET trong .env');
            return Command::FAILURE;
        }

        $records = DataList::whereDate('created_at', $targetDate)
            ->orderBy('id')
            ->get();

        $hash = hash('sha256', json_encode($records->toArray()) . $secret);

        $previous = DB::table('data_integrity_logs')
            ->orderBy('date', 'desc')
            ->first();

        $chainHash = hash('sha256', $hash . ($previous->chain_hash ?? ''));

        DB::table('data_integrity_logs')->updateOrInsert(
            ['date' => $targetDate],
            [
                'record_count' => $records->count(),
                'checksum' => $hash,
                'chain_hash' => $chainHash,
                'sealed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Log::info("Da niem phong du lieu ngay {$targetDate} voi hash {$hash}");
        $this->info("Da niem phong du lieu ngay {$targetDate}");

        return Command::SUCCESS;
    }
}
