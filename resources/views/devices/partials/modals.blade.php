<!-- Modal Thêm thiết bị -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                    <i class="fas fa-plus-circle me-2"></i> Thêm thiết bị mới
                </h4>
            </div>
            <form action="{{ route('devices.store') }}" method="POST" id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="ten_thiet_bi" class="form-label">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_thiet_bi" name="ten_thiet_bi" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="ma_so" class="form-label">Mã số <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ma_so" name="ma_so" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="nguong_dieu_khien" class="form-label">Ngưỡng điều khiển <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="nguong_dieu_khien" name="nguong_dieu_khien">
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_loaithietbi" class="form-label">Loại thiết bị <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_loaithietbi" name="id_loaithietbi" required>
                            <option value="">-- Chọn loại thiết bị --</option>
                            @foreach($deviceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai_thiet_bi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_phong" class="form-label">Phòng <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_phong" name="id_phong" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->ten_phong }} - {{ $room->vi_tri }} - {{ $room->khu_vuc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_trangthai" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_trangthai" name="id_trangthai" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->ten_trang_thai }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa thiết bị -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(42, 5, 122); color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Cập nhật thông tin thiết bị
                </h4>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_ten_thiet_bi">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_thiet_bi" name="ten_thiet_bi" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_ma_so">Mã số <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_so" name="ma_so" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_nguong_dieu_khien">Ngưỡng điều khiển <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="edit_nguong_dieu_khien" name="nguong_dieu_khien">
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_id_loaithietbi">Loại thiết bị <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_id_loaithietbi" name="id_loaithietbi" required>
                            <option value="">-- Chọn loại thiết bị --</option>
                            @foreach($deviceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai_thiet_bi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_id_phong">Phòng <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_id_phong" name="id_phong" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->ten_phong }} - {{ $room->vi_tri }} - {{ $room->khu_vuc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_id_trangthai">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_id_trangthai" name="id_trangthai" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->ten_trang_thai }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xóa thiết bị -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-trash-alt me-2"></i> Xóa thiết bị
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa thiết bị này không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <form action="" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Xóa
                    </button>
                </form>
            </div>
        </div>form
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #fd7e14; color: #ffffff;">
                <h4 class="modal-title m-0" id="importModalLabel" style="color: #ffffff;">
                    <i class="fas fa-file-import me-2"></i> Import danh sách thiết bị từ Excel
                </h4>
            </div>
            <form action="{{ route('devices.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong>
                        <ul class="mb-0">
                            <li>File Excel phải có các cột: Tên thiết bị, Mã số, Loại thiết bị, Phòng, Trạng thái</li>
                            <li>Loại thiết bị, Phòng và Trạng thái phải tồn tại trong hệ thống</li>
                            <li>Mã số thiết bị không được trùng lặp và bắt đầu bằng TB</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('devices.template')}}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-download"></i> Tải mẫu Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>