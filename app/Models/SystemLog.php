<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;
    protected $table = 'systemlogs';
    public $timestamps = false;

    protected $fillable = [
        'noi_dung_thuc_hien',
        'id_nguoidung',
        'thoi_gian_thuc_hien',
        'hash',
        'previous_hash'
    ];

    protected $casts = [
        'thoi_gian_thuc_hien' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            // 1. Lấy log cuối cùng
            $lastLog = SystemLog::orderBy('id', 'desc')->first();
            
            // 2. Lấy Hash cũ (Nếu chưa có thì dùng chuỗi khởi tạo)
            $prevHash = $lastLog ? $lastLog->hash : '0000000000000000000000000000000000000000000000000000000000000000';
            $log->previous_hash = $prevHash;

            // 3. Chuẩn bị dữ liệu để băm
            // Lưu ý: Format ngày tháng phải cố định để tránh lệch khi kiểm tra lại
            $timeString = $log->thoi_gian_thuc_hien->format('Y-m-d H:i:s');
            
            $dataToHash = $log->noi_dung_thuc_hien . 
                          $log->id_nguoidung . 
                          $timeString . 
                          $prevHash;

            // 4. [FIX QUAN TRỌNG] Dùng HMAC với APP_KEY của Laravel làm muối (Salt)
            // Kẻ tấn công nếu không có APP_KEY thì không thể giả mạo chuỗi này.
            $log->hash = hash_hmac('sha256', $dataToHash, env('APP_KEY'));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }
}