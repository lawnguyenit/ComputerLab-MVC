<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceType extends Model
{
    use HasFactory;
    protected $table = 'devicetypes';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_loai_thiet_bi',
        'mo_ta',
    ];

    /**
     * Lấy danh sách thiết bị thuộc loại này
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'id_loaithietbi');
    }
}