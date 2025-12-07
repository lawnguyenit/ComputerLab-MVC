<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'ten_phong',
        'khu_vuc',
        'vi_tri',
        'mo_ta',
    ];

    /**
     * Lấy danh sách khoa thuộc phòng này
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'id_phong');
    }

    /**
     * Lấy danh sách thiết bị trong phòng
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'id_phong');
    }

    /**
     * Lấy danh sách cảm biến trong phòng
     */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'id_phong');
    }

    /**
     * Lấy lịch sử sử dụng phòng
     */
    public function usageHistories(): HasMany
    {
        return $this->hasMany(RoomUsageHistory::class, 'id_phong');
    }

    /**
     * Lấy lịch cấm sử dụng phòng
     */
    public function restrictionSchedules(): HasMany
    {
        return $this->hasMany(RoomRestrictionSchedule::class, 'id_phong');
    }

    public function roomManager(): HasMany
    {
        return $this->hasMany(RoomManager::class, 'id_phong');
    }
}