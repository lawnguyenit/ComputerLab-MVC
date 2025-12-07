<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomManager extends Model
{
    use HasFactory;
    protected $table = 'RoomManagers';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_nguoidung',
        'id_phong',
        'mo_ta',
    ];

    /**
     * Lấy thông tin người dùng quản lý phòng
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }

    /**
     * Lấy thông tin phòng được quản lý
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'id_phong');
    }
}