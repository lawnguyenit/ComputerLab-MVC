<!-- Modal Xóa lịch cấm sử dụng thiết bị -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-trash-alt me-2"></i> Xóa lịch cấm sử dụng thiết bị
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa lịch cấm sử dụng thiết bị này không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times me-1"></i>Hủy</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i>Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm lịch cấm sử dụng thiết bị -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                    <i class="fas fa-plus-circle"></i> Thêm lịch cấm sử dụng thiết bị
                </h4>
            </div>
            <form action="{{ route('device-restrictions.store') }}" method="POST" id="addForm">
                @csrf
                <div class="modal-body"> 
                    <div class="form-group mb-3">
                        <label for="id_phong">Phòng <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="id_phong" name="id_phong" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('id_phong') == $room->id ? 'selected' : '' }}>
                                    {{ $room->ten_phong }} - {{ $room->vi_tri }} - {{ $room->khu_vuc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="id_thietbi">Thiết bị <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="id_thietbi" name="id_thietbi" required>
                            <option value="">-- Chọn thiết bị --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}" data-room="{{ $device->id_phong }}" {{ old('id_thietbi') == $device->id ? 'selected' : '' }}>
                                    {{ $device->ten_thiet_bi }} - {{ $device->ma_so }} - {{ $device->room->ten_phong }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="noi_dung_cam_su_dung">Nội dung cấm sử dụng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="noi_dung_cam_su_dung" name="noi_dung_cam_su_dung" value="{{ old('noi_dung_cam_su_dung') }}" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="thoi_gian_bat_dau">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="thoi_gian_bat_dau" name="thoi_gian_bat_dau" value="{{ old('thoi_gian_bat_dau') }}" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="thoi_gian_ket_thuc">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="thoi_gian_ket_thuc" name="thoi_gian_ket_thuc" value="{{ old('thoi_gian_ket_thuc') }}" required>
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

<!-- Modal Chỉnh sửa lịch cấm sử dụng thiết bị -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(42, 5, 122); color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Cập nhật lịch cấm sử dụng thiết bị
                </h4>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="form-group mb-3">
                        <label for="edit_id_phong">Phòng <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="edit_id_phong" name="id_phong" required>
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->ten_phong }} - {{ $room->vi_tri }} - {{ $room->khu_vuc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_id_thietbi">Thiết bị <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="edit_id_thietbi" name="id_thietbi" required>
                            <option value="">-- Chọn thiết bị --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}" data-room="{{ $device->id_phong }}">
                                    {{ $device->ten_thiet_bi }} - {{ $device->ma_so }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_noi_dung_cam_su_dung">Nội dung cấm sử dụng <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_noi_dung_cam_su_dung" name="noi_dung_cam_su_dung" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_thoi_gian_bat_dau">Thời gian bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="edit_thoi_gian_bat_dau" name="thoi_gian_bat_dau" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_thoi_gian_ket_thuc">Thời gian kết thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="edit_thoi_gian_ket_thuc" name="thoi_gian_ket_thuc" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #fd7e14; color: #ffffff;">
                <h4 class="modal-title m-0" id="importModalLabel" style="color: #ffffff;">
                    <i class="fas fa-file-import me-2"></i> Import lịch cấm sử dụng thiết bị từ Excel
                </h4>
            </div>
            <form action="{{ route('device-restrictions.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong>
                        <ul class="mb-0">
                            <li>File phải có định dạng .xlsx hoặc .xls</li>
                            <li>Dữ liệu phải theo đúng mẫu (Mã thiết bị, Nội dung cấm sử dụng, Thời gian bắt đầu, Thời gian kết thúc)</li>
                            <li>Thời gian phải theo định dạng YYYY-MM-DD HH:MM:SS</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('device-restrictions.template') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-download"></i> Tải mẫu Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>