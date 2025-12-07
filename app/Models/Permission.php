<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_quyen',
        'mo_ta',
    ];

    /**
     * Lấy danh sách người dùng có quyền này
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'userpermissions', 'id_quyen', 'id_nguoidung');
    }
}