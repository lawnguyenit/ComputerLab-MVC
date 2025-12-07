@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-4">
            <div class="ibox float-e-margins shadow-sm rounded">
                <div class="ibox-title my-ibox-title border-bottom">
                    <h2 class="mb-0 py-2">
                        <i class="fas fa-qrcode mr-2"></i>
                        Mã QR của bạn
                    </h2>
                </div>
                <div class="ibox-content text-center py-4">
                    <div class="qr-container mb-3">
                        <img 
                            src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(256)->generate($user->sdt)) }}"                            alt="Mã QR Code" 
                            class="img-fluid rounded shadow-sm hover-zoom"
                            style="max-width: 250px; width: 250px; height: 250px; object-fit: contain; transition: transform .2s;"
                        >
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-key mr-1"></i>
                        <small>Mã truy cập phòng</small>
                    </p>
                    <div class="mt-3">
                        <button id="downloadQrJpg" class="btn btn-sm btn-primary">
                            <i class="fas fa-download mr-1"></i> Tải QR (SVG)
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Thông tin cá nhân</h2>
                </div>
                <div class="ibox-content">
                    <form method="POST" action="{{ route('profile.update') }}" class="form-horizontal" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Tên tài khoản <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="ten_tai_khoan" class="form-control @error('ten_tai_khoan') is-invalid @enderror" 
                                    value="{{ old('ten_tai_khoan', $user->ten_tai_khoan) }}" required>
                                @error('ten_tai_khoan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Họ và tên <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="ho_ten" class="form-control @error('ho_ten') is-invalid @enderror" 
                                    value="{{ old('ho_ten', $user->ho_ten) }}" required>
                                @error('ho_ten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Email <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Số điện thoại</label>
                            <div class="col-sm-9">
                                <input type="tel" name="sdt" class="form-control @error('sdt') is-invalid @enderror" pattern="[0-9]{10,12}"
                                    value="{{ old('sdt', $user->sdt) }}">
                                @error('sdt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Khoa công tác</label>
                            <div class="col-sm-9">
                                <select name="id_khoa" class="form-control @error('id_khoa') is-invalid @enderror">
                                    <option value="">-- Chọn khoa --</option>
                                    @foreach ($khoas as $khoa)
                                        <option value="{{ $khoa->id }}" {{ $user->id_khoa == $khoa->id ? 'selected' : '' }}>
                                            {{ $khoa->ten_khoa }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_khoa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Mật khẩu mới</label>
                            <div class="col-sm-9">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                <small class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-4">
                            <label class="col-sm-3 col-form-label">Xác nhận mật khẩu</label>
                            <div class="col-sm-9">
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group row mb-0">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $(document).ready(function() {
        // Hiển thị tên file khi chọn ảnh
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
        
        // Chức năng tải QR code dưới dạng JPG
        $('#downloadQrJpg').on('click', function(e) {
            e.preventDefault();
            
            // Lấy đường dẫn SVG từ thẻ img
            const svgSrc = document.querySelector('.qr-container img').src;
            
            // Tạo link tải xuống
            const link = document.createElement('a');
            link.href = svgSrc;
            link.download = 'QR_Code_{{ $user->ten_tai_khoan }}.svg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
</script>
@endsection