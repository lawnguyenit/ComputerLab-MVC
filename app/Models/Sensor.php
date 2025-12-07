<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    use HasFactory;
    protected $table ='sensors';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_cam_bien',
        'ma_so',
        'id_loaicambien',
        'id_phong',
        'id_trangthai',
    ];

    /**
     * Lấy thông tin loại cảm biến
     */
    public function sensorType(): BelongsTo
    {
        return $this->belongsTo(SensorType::class, 'id_loaicambien');
    }

    /**
     * Lấy thông tin phòng chứa cảm biến
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }

    /**
     * Lấy thông tin trạng thái cảm biến
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'id_trangthai');
    }

    /**
     * Lấy danh sách dữ liệu thu thập từ cảm biến
     */
    public function dataLists(): HasMany
    {
        return $this->hasMany(DataList::class, 'id_cambien');
    }
}