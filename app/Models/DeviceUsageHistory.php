<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceUsageHistory extends Model
{
    use HasFactory;
    protected $table = 'deviceusagehistory';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'id_thietbi',
        'id_nguoidung',
        'id_trangthaisudung',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];

    /**
     * Lấy thông tin thiết bị được sử dụng
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'id_thietbi');
    }

    /**
     * Lấy thông tin người dùng sử dụng thiết bị
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }

    /**
     * Lấy thông tin trạng thái sử dụng
     */
    public function usageStatus(): BelongsTo
    {
        return $this->belongsTo(UsageStatus::class, 'id_trangthaisudung');
    }
}