<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ScanSystemIntegrity extends Command
{
    // Tên lệnh để gọi trong terminal hoặc scheduler
    protected $signature = 'security:scan-integrity';

    // Mô tả lệnh
    protected $description = 'Quét toàn vẹn dữ liệu Logs và gửi cảnh báo nếu phát hiện xâm nhập';

    public function handle()
    {
        $this->info('Bắt đầu quét toàn vẹn hệ thống...');
        
        $logs = SystemLog::orderBy('id', 'asc')->get();
        $errors = [];

        foreach ($logs as $key => $log) {
            if ($key === 0) continue; // Bỏ qua Genesis block

            $prevLog = $logs[$key - 1];

            // 1. Kiểm tra đứt gãy chuỗi
            if ($log->previous_hash !== $prevLog->hash) {
                $msg = "Phát hiện ĐỨT GÃY chuỗi tại Log ID #{$log->id}. Log trước đó (#{$prevLog->id}) có thể đã bị xóa/sửa.";
                $errors[] = $msg;
                $this->error($msg); // In ra màn hình console
            }

            // 2. Kiểm tra sai lệch nội dung (HMAC)
            $timeString = $log->thoi_gian_thuc_hien->format('Y-m-d H:i:s');
            $dataToCheck = $log->noi_dung_thuc_hien . 
                           $log->id_nguoidung . 
                           $timeString . 
                           $log->previous_hash;
            
            // Lưu ý: Cần đảm bảo APP_KEY trong .env giống hệt lúc tạo log
            $recalculatedHash = hash_hmac('sha256', $dataToCheck, env('APP_KEY'));

            if ($recalculatedHash !== $log->hash) {
                $msg = "Dữ liệu bị SỬA ĐỔI trái phép tại Log ID #{$log->id}.";
                $errors[] = $msg;
                $this->error($msg);
            }
        }

        // Nếu có lỗi -> Gửi mail báo động
        if (count($errors) > 0) {
            $this->sendAlertEmail($errors);
            $this->error('Đã phát hiện rủi ro! Email cảnh báo đã được gửi.');
        } else {
            $this->info('Hệ thống toàn vẹn. Không phát hiện bất thường.');
        }
    }

    private function sendAlertEmail($errors)
    {
        $adminEmail = env('SECURITY_ALERT_MAIL');

        if (!$adminEmail) {
            $this->warn('Chưa cấu hình SECURITY_ALERT_MAIL trong file .env');
            return;
        }

        try {
            $details = implode("<br>", $errors);
            
            // Tận dụng lại view email cảnh báo mà ta đã tạo ở bước trước
            Mail::send('emails.security_alert', [
                'type' => 'QUÉT ĐỊNH KỲ (SCHEDULED SCAN) - PHÁT HIỆN LOG BỊ CAN THIỆP',
                'detail' => $details
            ], function($message) use ($adminEmail) {
                $message->to($adminEmail)
                       ->subject('🚨 BÁO ĐỘNG ĐỎ: HỆ THỐNG DỮ LIỆU ĐÃ BỊ XÂM NHẬP');
            });

            Log::alert("Đã gửi mail cảnh báo bảo mật tới: $adminEmail");

        } catch (\Exception $e) {
            Log::error("Lỗi gửi mail cảnh báo: " . $e->getMessage());
        }
    }
}