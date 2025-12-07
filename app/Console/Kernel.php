<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Chạy vào 23:59 hàng ngày
        $schedule->command('email:daily-report')->dailyAt('23:59');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
    protected function schedule_scan(Schedule $schedule): void
    {
        // Giữ nguyên các lệnh cũ của bạn (như SendDailySensorDataReport)
        $schedule->command('app:send-daily-sensor-data-report')->dailyAt('07:00');
        $schedule->command('mqtt:subscribe')->everyMinute(); // Ví dụ nếu có

        // --- THÊM DÒNG NÀY ---
        // Chạy quét bảo mật mỗi tiếng 1 lần
        $schedule->command('security:scan-integrity')
            ->hourly()
            ->withoutOverlapping(); // Không chạy chồng chéo nếu lần trước chưa xong

        // Hoặc nếu muốn demo nhanh thấy kết quả thì dùng ->everyMinute()
    }
}