<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorType extends Model
{
    use HasFactory;
    protected $table ='sensortypes';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_loai_cam_bien',
        'mo_ta',
    ];

    /**
     * Lấy danh sách cảm biến thuộc loại này
     */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'id_loaicambien');
    }
}