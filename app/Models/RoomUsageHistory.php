<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUsageHistory extends Model
{
    use HasFactory;
    protected $table = 'roomusagehistory';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'id_phong',
        'id_nguoidung',
        'id_trangthaisudung',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];

    /**
     * Lấy thông tin phòng được sử dụng
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }

    /**
     * Lấy thông tin người dùng sử dụng phòng
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