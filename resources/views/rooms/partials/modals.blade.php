<!-- Modal Thêm phòng -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                    <i class="fas fa-plus-circle"></i> Thêm phòng mới
                </h4>
            </div>
            <form action="{{ route('rooms.store') }}" method="POST" id="addForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="ten_phong">Tên phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_phong" name="ten_phong" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="khu_vuc">Khu vực <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="khu_vuc" name="khu_vuc" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="vi_tri">Vị trí <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="vi_tri" name="vi_tri" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="mo_ta">Mô tả</label>
                        <textarea class="form-control" id="mo_ta" name="mo_ta" rows="3"></textarea>
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

<!-- Modal Sửa phòng -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(42, 5, 122); color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Cập nhật thông tin phòng
                </h4>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="edit_ten_phong">Tên phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_phong" name="ten_phong" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_khu_vuc">Khu vực <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_khu_vuc" name="khu_vuc" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_vi_tri">Vị trí <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_vi_tri" name="vi_tri" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="edit_mo_ta">Mô tả</label>
                        <textarea class="form-control" id="edit_mo_ta" name="mo_ta" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
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

<!-- Modal Xóa phòng -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-trash-alt me-2"></i> Xóa phòng
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa phòng này không?</p>
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
                    <i class="fas fa-file-import me-2"></i> Import danh sách phòng từ Excel
                </h4>
            </div>
            <form action="{{ route('rooms.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong>
                        <ul class="mb-0">
                            <li>File Excel phải có các cột: Tên phòng, Khu vực, Vị trí, Mô tả</li>
                            <li>Tên phòng không được trùng lặp</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('rooms.template') }}" class="btn btn-sm btn-outline-primary">
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