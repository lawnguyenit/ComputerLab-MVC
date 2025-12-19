<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    use HasFactory;
    protected $table = 'systemlogs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'noi_dung_thuc_hien',
        'id_nguoidung',
        'thoi_gian_thuc_hien',
    ];

    protected $casts = [
        'thoi_gian_thuc_hien' => 'datetime',
    ];

    /**
     * Lấy thông tin người dùng thực hiện hành động
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }
}