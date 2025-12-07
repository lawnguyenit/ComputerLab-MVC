<!-- Modal Thêm người dùng -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #28a745; color: #ffffff;">
                <h4 class="modal-title m-0" id="addModalLabel" style="color: #ffffff;">
                    <i class="fas fa-plus-circle me-2"></i> Thêm người dùng mới
                </h4>
            </div>
            <form id="addUserForm" action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Tên tài khoản <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ten_tai_khoan" name="ten_tai_khoan" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Khoa <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_khoa" name="id_khoa" required>
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->ten_khoa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" class="form-control" id="sdt" name="sdt">
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Quyền <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_quyen" name="id_quyen" required>
                                    <option value="">-- Chọn quyền --</option>
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->id }}">{{ $permission->ten_quyen }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
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

<!-- Modal Sửa người dùng -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #0d6efd; color: #ffffff;">
                <h4 class="modal-title m-0" id="editModalLabel" style="color: #ffffff;">
                    <i class="fas fa-edit me-2"></i> Cấp quyền tài khoản
                </h4>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Tên tài khoản</label>
                                <span class="form-control-plaintext" id="edit_ten_tai_khoan_display"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Họ tên</label>
                                <span class="form-control-plaintext" id="edit_ho_ten_display"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <span class="form-control-plaintext" id="edit_sdt_display"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                        <div class="form-group mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <span class="form-control-plaintext" id="edit_email_display"></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Khoa</label>
                                <span class="form-control-plaintext" id="edit_khoa_display"></span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Phân quyền người dùng <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_id_phan_quyen" name="edit_id_phan_quyen" required>
                            <option value="">-- Chọn quyền --</option>
                            @foreach($permissions as $permission)
                                <option value="{{ $permission->id }}">{{ $permission->ten_quyen }}</option>
                            @endforeach
                        </select>
                    </div>
                            
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Đóng
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Cập nhật quyền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xóa người dùng -->
<div class="modal fade" id="blockUserModal" tabindex="-1" role="dialog" aria-labelledby="blockUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-lock me-2"></i> Khóa tài khoản người dùng
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn khóa tài khoản người dùng này không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <form id="blockUserForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-lock me-1"></i> Khóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color:rgb(196, 9, 9); color: #ffffff;">
                <h4 class="modal-title m-0" id="deleteModalLabel" style="color: #ffffff;">
                    <i class="fas fa-trash me-2"></i> Xóa tài khoản người dùng
                </h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa tài khoản người dùng này không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteUserForm" method="POST" action="">
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
<div class="modal fade" id="importUserModal" tabindex="-1" role="dialog" aria-labelledby="importUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background-color: #fd7e14; color: #ffffff;">
                <h4 class="modal-title m-0" id="importModalLabel" style="color: #ffffff;">
                    <i class="fas fa-file-import me-2"></i> Import danh sách users từ Excel
                </h4>
            </div>
            <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Chọn file Excel/CSV</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                    <div class="alert alert-info mt-3">
                        <strong>Lưu ý:</strong>
                        <ul class="mb-0">
                            <li>File Excel phải có các cột: Tên tài khoản, Email, Mật khẩu, Họ tên, Số điện thoại, Mã khoa</li>
                            <li>Mã khoa phải tồn tại trong hệ thống</li>
                            <li>Email và tên tài khoản không được trùng lặp</li>
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('users.template') }}" class="btn btn-sm btn-outline-primary">
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