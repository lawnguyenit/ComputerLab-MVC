@extends('layouts.app')
@section('title', 'Tạo mã QR')

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                    <h2><i class="fa fa-qrcode me-2"></i>Tạo mã QR</h2>
                    <a href="{{ route('qrcode.list') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-list me-1"></i>Danh sách mã QR
                    </a>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-lg">
                                <div class="card-body p-4">
                                    <h3 class="mb-3">Nhập nội dung</h3>
                                    <form id="qrForm">
                                        <div class="mb-3">
                                            <label for="text" class="form-label">Nội dung cần chuyển thành mã QR</label>
                                            <textarea class="form-control" id="text" name="text" rows="5" required></textarea>
                                            <div class="form-text">Nhập văn bản, URL, thông tin liên hệ hoặc bất kỳ dữ liệu nào bạn muốn chuyển thành mã QR.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="size" class="form-label">Kích thước (px)</label>
                                            <input type="range" class="form-range" id="size" name="size" min="100" max="500" step="50" value="300">
                                            <div class="d-flex justify-content-between">
                                                <span>100px</span>
                                                <span id="sizeValue">300px</span>
                                                <span>500px</span>
                                            </div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-qrcode me-1"></i>Tạo mã QR
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-lg h-100">
                                <div class="card-body p-4 text-center">
                                    <h3 class="mb-3">Mã QR của bạn</h3>
                                    <div id="qrResult" class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                                        <div class="text-muted">
                                            <i class="fa fa-info-circle me-1"></i>Nhập nội dung và nhấn "Tạo mã QR" để tạo mã QR của bạn.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hiển thị giá trị kích thước
        const sizeInput = document.getElementById('size');
        const sizeValue = document.getElementById('sizeValue');
        
        sizeInput.addEventListener('input', function() {
            sizeValue.textContent = this.value + 'px';
        });
        
        // Xử lý form tạo QR
        const qrForm = document.getElementById('qrForm');
        const qrResult = document.getElementById('qrResult');
        
        qrForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const text = document.getElementById('text').value;
            const size = document.getElementById('size').value;
            
            if (!text) {
                alert('Vui lòng nhập nội dung cần chuyển thành mã QR');
                return;
            }
            
            // Hiển thị trạng thái đang tải
            qrResult.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tạo...</span></div><p class="mt-2">Đang tạo mã QR...</p>';
            
            // Gửi request AJAX để tạo mã QR
            fetch('{{ route('qrcode.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    text: text,
                    size: size
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị mã QR đã tạo
                    qrResult.innerHTML = `
                        <div class="mb-3">
                            <img src="${data.path}" alt="QR Code" class="img-fluid" style="max-width: 100%; max-height: 300px;">
                        </div>
                        <div class="mb-3">
                            <a href="${data.path}" download="${data.filename}" class="btn btn-success me-2">
                                <i class="fa fa-download me-1"></i>Tải xuống
                            </a>
                            <a href="${data.path}" target="_blank" class="btn btn-info">
                                <i class="fa fa-external-link me-1"></i>Mở trong tab mới
                            </a>
                        </div>
                        <div class="alert alert-success">
                            Mã QR đã được lưu vào thư mục keyqrcode
                        </div>
                    `;
                } else {
                    qrResult.innerHTML = `<div class="alert alert-danger">${data.message || 'Có lỗi xảy ra khi tạo mã QR'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                qrResult.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tạo mã QR</div>';
            });
        });
    });
</script>
@endsection