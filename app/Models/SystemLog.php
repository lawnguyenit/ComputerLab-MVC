<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;
    protected $table = 'systemlogs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'noi_dung_thuc_hien',
        'id_nguoidung',
        'thoi_gian_thuc_hien',
        'hash',          // Thêm
        'previous_hash'  // Thêm
    ];

    protected $casts = [
        'thoi_gian_thuc_hien' => 'datetime',
    ];

    /**
     * Lấy thông tin người dùng thực hiện hành động
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            // 1. Lấy dòng log cuối cùng được tạo ra
            $lastLog = SystemLog::orderBy('id', 'desc')->first();

            // 2. Lấy hash của dòng trước (Nếu là dòng đầu tiên thì dùng genesis hash)
            $prevHash = $lastLog ? $lastLog->hash : '00000000000000000000000000000000';
            
            $log->previous_hash = $prevHash;

            // 3. Tạo hash cho dòng hiện tại
            // Hash = SHA256(Nội dung + UserID + Thời gian + PreviousHash)
            // Việc đưa PreviousHash vào đây tạo nên sự liên kết dây chuyền
            $dataToHash = $log->noi_dung_thuc_hien . 
                          $log->id_nguoidung . 
                          $log->thoi_gian_thuc_hien . 
                          $prevHash;
                          
            $log->hash = hash('sha256', $dataToHash);
        });
    }
}