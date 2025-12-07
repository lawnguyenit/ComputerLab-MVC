<!-- Modal Thêm cảm biến -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                    <i class="fas fa-plus-circle me-2"></i> Thêm cảm biến mới
                </h4>
            </div>
            <form action="{{ route('sensors.store') }}" method="POST" id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Tên cảm biến <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_cam_bien" name="ten_cam_bien" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Mã số <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ma_so" name="ma_so" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Loại cảm biến <span class="text-danger">*</span></label>
                        <select class="form-control" id="id_loaicambien" name="id_loaicambien" required>
                            <option value="">-- Chọn loại cảm biến --</option>
                            @foreach($sensorTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai_cam_bien }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Phòng <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
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
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa cảm biến -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Cập nhật thông tin cảm biến
                </h4>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Tên cảm biến <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_cam_bien" name="ten_cam_bien" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Mã số <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_so" name="ma_so" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Loại cảm biến <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_id_loaicambien" name="id_loaicambien" required>
                            <option value="">-- Chọn loại cảm biến --</option>
                            @foreach($sensorTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->ten_loai_cam_bien }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Phòng <span class="text-danger">*</span></label>
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
                        <label class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
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

<!-- Modal Xóa cảm biến -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-trash-alt me-2"></i> Xóa cảm biến
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa cảm biến này không?</p>
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
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #fd7e14; color: #ffffff;">
                <h4 class="modal-title m-0" id="importModalLabel" style="color: #ffffff;">
                    <i class="fas fa-file-import me-2"></i> Import danh sách cảm biến từ Excel
                </h4>
            </div>
            <form action="{{ route('sensors.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong>
                        <ul class="mb-0">
                            <li>File Excel phải có các cột: Tên cảm biến, Mã số, Loại cảm biến, Phòng, Trạng thái</li>
                            <li>Loại cảm biến, Phòng và Trạng thái phải tồn tại trong hệ thống</li>
                            <li>Mã số cảm biến không được trùng lặp và bắt đầu bằng CB</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('sensors.template') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Tải mẫu Excel
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