<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;
    protected $table = 'statuses';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_trang_thai',
        'mo_ta',
    ];

    /**
     * Lấy danh sách thiết bị có trạng thái này
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'id_trangthai');
    }

    /**
     * Lấy danh sách cảm biến có trạng thái này
     */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'id_trangthai');
    }
}