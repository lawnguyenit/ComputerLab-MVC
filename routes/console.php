<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Đăng ký lệnh gửi báo cáo hàng ngày
Artisan::command('email:daily-report', function () {
    $this->call('report:daily-sensor-data');
})->purpose('Gửi báo cáo dữ liệu cảm biến hàng ngày qua email');
