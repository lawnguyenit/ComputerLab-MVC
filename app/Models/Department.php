<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_khoa',
        'ten_viet_tat',
        'id_phong',
        'email',
        'sdt',
    ];

    /**
     * Lấy thông tin phòng của khoa
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }

    /**
     * Lấy danh sách người dùng thuộc khoa
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_khoa');
    }
}