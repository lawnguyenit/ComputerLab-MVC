<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    use HasFactory;
    protected $table = 'userpermissions';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_nguoidung',
        'id_quyen',
    ];

    /**
     * Lấy thông tin người dùng
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoidung');
    }

    /**
     * Lấy thông tin quyền
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'id_quyen');
    }
}