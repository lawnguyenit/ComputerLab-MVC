<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsageStatus extends Model
{
    use HasFactory;
    protected $table = 'usagestatuses';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_trang_thai',
        'mo_ta',
    ];

    /**
     * Lấy lịch sử sử dụng phòng với trạng thái này
     */
    public function roomUsageHistories(): HasMany
    {
        return $this->hasMany(RoomUsageHistory::class, 'id_trangthaisudung');
    }

    /**
     * Lấy lịch sử sử dụng thiết bị với trạng thái này
     */
    public function deviceUsageHistories(): HasMany
    {
        return $this->hasMany(DeviceUsageHistory::class, 'id_trangthaisudung');
    }
}