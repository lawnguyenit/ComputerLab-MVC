<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageUnit extends Model
{
    use HasFactory;
    protected $table = 'storageunits';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ten_don_vi_luu_tru',
        'mo_ta',
    ];

    /**
     * Lấy danh sách dữ liệu sử dụng đơn vị lưu trữ này
     */
    public function dataLists(): HasMany
    {
        return $this->hasMany(DataList::class, 'id_donviluutru');
    }
}