@extends('layouts.app')
@section('title', 'Danh sách mã QR')

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                    <h2><i class="fa fa-list me-2"></i>Danh sách mã QR</h2>
                    <a href="{{ route('qrcode.index') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-qrcode me-1"></i>Tạo mã QR mới
                    </a>
                </div>
                <div class="ibox-content">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if(count($qrCodes) > 0)
                        <div class="row">
                            @foreach($qrCodes as $qr)
                                <div class="col-md-3 mb-4">
                                    <div class="card border-0 shadow-sm rounded-lg h-100">
                                        <div class="card-body p-3 text-center">
                                            <img src="{{ $qr['path'] }}" alt="QR Code" class="img-fluid mb-3" style="max-height: 200px;">
                                            <p class="mb-1 text-truncate">{{ $qr['filename'] }}</p>
                                            <small class="text-muted">{{ $qr['created_at'] }}</small>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 p-3">
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ $qr['path'] }}" download="{{ $qr['filename'] }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                                <a href="{{ $qr['path'] }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <form action="{{ route('qrcode.delete', $qr['filename']) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã QR này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                            <h4>Chưa có mã QR nào được tạo</h4>
                            <p class="text-muted">Hãy tạo mã QR đầu tiên của bạn bằng cách nhấn vào nút "Tạo mã QR mới"</p>
                            <a href="{{ route('qrcode.index') }}" class="btn btn-primary">
                                <i class="fa fa-qrcode me-1"></i>Tạo mã QR mới
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection