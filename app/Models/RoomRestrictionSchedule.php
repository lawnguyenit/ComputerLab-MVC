<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRestrictionSchedule extends Model
{
    use HasFactory;
    protected $table = 'roomrestrictionschedule';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'noi_dung_cam_su_dung',
        'id_phong',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];

    /**
     * Lấy thông tin phòng bị cấm sử dụng
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }
}