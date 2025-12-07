<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataList extends Model
{
    use HasFactory;

    protected $table = 'datalist';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_cambien',
        'du_lieu_thu_thap',
        'id_donviluutru',
        'thoi_gian_thu_thap',
    ];

    protected $casts = [
        'thoi_gian_thu_thap' => 'datetime',
    ];

    /**
     * Lấy thông tin cảm biến thu thập dữ liệu
     */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'id_cambien');
    }

    /**
     * Lấy thông tin đơn vị lưu trữ
     */
    public function storageUnit(): BelongsTo
    {
        return $this->belongsTo(StorageUnit::class, 'id_donviluutru');
    }
    
    /**
     * Lọc dữ liệu theo khoảng thời gian
     */
    public function scopeInTimeRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('thoi_gian_thu_thap', [$startDate, $endDate]);
    }

    /**
     * Lọc dữ liệu theo cảm biến
     */
    public function scopeBySensor($query, $sensorId)
    {
        return $query->where('id_cambien', $sensorId);
    }
    
    /**
     * Lọc dữ liệu theo phòng
     */
    public function scopeByRoom($query, $roomId)
    {
        return $query->whereHas('sensor', function($q) use ($roomId) {
            $q->where('id_phong', $roomId);
        });
    }
    
    /**
     * Lấy dữ liệu mới nhất
     */
    public function scopeLatest($query, $limit = 10)
    {
        return $query->orderBy('thoi_gian_thu_thap', 'desc')->limit($limit);
    }
}