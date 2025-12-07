<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;
    protected $table = 'devices';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;


    protected $fillable = [
        'ten_thiet_bi',
        'ma_so',
        'id_loaithietbi',
        'id_phong',
        'id_trangthai',
        'nguong_dieu_khien'
    ];

    /**
     * Lấy thông tin loại thiết bị
     */
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'id_loaithietbi');
    }

    /**
     * Lấy thông tin phòng chứa thiết bị
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }

    /**
     * Lấy thông tin trạng thái thiết bị
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'id_trangthai');
    }

    /**
     * Lấy lịch sử sử dụng thiết bị
     */
    public function usageHistories(): HasMany
    {
        return $this->hasMany(DeviceUsageHistory::class, 'id_thietbi');
    }

    /**
     * Lấy lịch cấm sử dụng thiết bị
     */
    public function restrictionSchedules(): HasMany
    {
        return $this->hasMany(DeviceRestrictionSchedule::class, 'id_thietbi');
    }
}