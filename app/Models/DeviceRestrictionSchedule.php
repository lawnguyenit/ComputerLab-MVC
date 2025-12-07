<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRestrictionSchedule extends Model
{
    use HasFactory;
    protected $table = 'devicerestrictionschedule';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'noi_dung_cam_su_dung',
        'id_thietbi',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];

    /**
     * Lấy thông tin thiết bị bị cấm sử dụng
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'id_thietbi');
    }
}