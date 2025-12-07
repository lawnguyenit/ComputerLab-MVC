<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Department;
use App\Models\Permission;
use App\Models\RoomUsageHistory;
use App\Models\DeviceUsageHistory;
use App\Models\UserRestrictionSchedule;
use App\Models\SystemLog;
use App\Models\RoomManager;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_tai_khoan',
        'email',
        'password',
        'ho_ten',
        'sdt',
        'id_khoa',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Lấy thông tin khoa của người dùng
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'id_khoa');
    }

    /**
     * Lấy danh sách quyền của người dùng
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'userpermissions', 'id_nguoidung', 'id_quyen');
    }

    /**
     * Lấy lịch sử sử dụng phòng của người dùng
     */
    public function roomUsageHistories(): HasMany
    {
        return $this->hasMany(RoomUsageHistory::class, 'id_nguoidung');
    }

    /**
     * Lấy lịch sử sử dụng thiết bị của người dùng
     */
    public function deviceUsageHistories(): HasMany
    {
        return $this->hasMany(DeviceUsageHistory::class, 'id_nguoidung');
    }

    /**
     * Lấy lịch cấm sử dụng tài khoản
     */
    public function restrictionSchedules(): HasMany
    {
        return $this->hasMany(UserRestrictionSchedule::class, 'id_nguoidung');
    }

    /**
     * Lấy log hoạt động của người dùng
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SystemLog::class, 'id_nguoidung');
    }

    public function userManager(): HasMany
    {
        return $this->hasMany(RoomManager::class, 'id_nguoidung');
    }

    public function roomManager(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'roommanagers', 'id_nguoidung', 'id_phong');
    }   
    
    /**
     * Get permission names for the user
     * 
     * @return array
     */
    public static function getPermissionNames($id_user): string
    {
        $id_permissions = UserPermission::where('id_nguoidung', $id_user)->first()->id_quyen;
        $permission = Permission::where('id', $id_permissions)->first();
        return $permission->ten_quyen;
    }
}
