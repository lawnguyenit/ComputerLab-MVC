@extends('layouts.app')
@section('title', 'Dữ liệu cảm biến')
@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Dữ liệu cảm biến theo phòng</h2>
                </div>
                <div class="ibox-content">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="{{ route('datalist.index') }}" method="GET" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="room_id" class="mr-2 font-weight-bold">Chọn phòng:</label>
                                    <select name="room_id" id="room_id" class="form-control select2" onchange="this.form.submit()">
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" {{ isset($selectedRoom) && $selectedRoom->id == $room->id ? 'selected' : '' }}>
                                                {{ $room->ten_phong }} - {{ $room->khu_vuc}} - {{ $room->vi_tri }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if(isset($selectedRoom))
                    <div class="room-info mb-4">
                        <div class="card shadow-sm rounded-lg">
                            <div class="card-header bg-light d-flex align-items-center">
                                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i> Thông tin phòng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Tên phòng:</strong> {{ $selectedRoom->ten_phong }}</p>
                                        <p><strong>Số lượng thiết bị:</strong> {{ $devices->count() }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Khu vực:</strong> {{ $selectedRoom->khu_vuc }}</p>
                                        <p><strong>Số cảm biến:</strong> {{ $sensors->count() }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Vị trí:</strong> {{ $selectedRoom->vi_tri }}</p>
                                        <p><strong>Trạng thái:</strong> 
                                           @if($roomusagehistory->contains('id_phong', $selectedRoom->id))
                                               <span class="badge badge-success">Đang sử dụng</span>
                                           @else
                                               <span class="badge badge-danger">Chưa sử dụng</span>
                                           @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card shadow-sm rounded-lg">
                                <div class="card-header bg-light d-flex align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i> Bộ lọc dữ liệu</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('datalist.index') }}" method="GET" class="form-inline">
                                        <input type="hidden" name="room_id" value="{{ $selectedRoom->id }}">
                                        
                                        <div class="form-group mr-3 mb-3">
                                            <label for="sensor_id" class="mr-2 form-label fw-bold">Lọc theo cảm biến:</label>
                                            <select name="sensor_id" id="sensor_id" class="form-control select2">
                                                <option value="">Tất cả cảm biến</option>
                                                @foreach($sensors as $sensor)
                                                    <option value="{{ $sensor->id }}" {{ $selectedSensorId == $sensor->id ? 'selected' : '' }}>
                                                        {{ $sensor->ten_cam_bien }} - {{ $sensor-> sensorType -> ten_loai_cam_bien }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mr-3 mb-3">
                                            <label for="start_date" class="mr-2 form-label fw-bold">Từ ngày:</label>
                                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                                        </div>
                                        
                                        <div class="form-group mr-3 mb-3">
                                            <label for="end_date" class="mr-2 form-label fw-bold">Đến ngày:</label>
                                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                                        </div>
                                        
                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i> Lọc dữ liệu
                                        </button>
                                        <a href="{{ route('datalist.index', ['room_id' => $selectedRoom->id]) }}" class="btn btn-secondary ml-2">
                                            <i class="fas fa-redo me-1"></i>
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cảm biến</th>
                                    <th>Dữ liệu thu thập</th>
                                    <th>Đơn vị lưu trữ</th>
                                    <th>Thời gian thu thập</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dataList as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->sensor->ten_cam_bien }}</td>
                                    <td>{{ $data->du_lieu_thu_thap }}</td>
                                    <td>{{ $data->storageUnit->ten_don_vi_luu_tru }}</td>
                                    <td>{{ $data->thoi_gian_thu_thap->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $dataList->appends([
                            'room_id' => $selectedRoom->id, 
                            'sensor_id' => $selectedSensorId,
                            'start_date' => $startDate,
                            'end_date' => $endDate
                        ])->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Khởi tạo select2 cho các dropdown
        $('.select2').select2({
            width: '100%'
        });
        
        // Tự động làm mới dữ liệu mỗi 60 giây nếu không có bộ lọc nào được áp dụng
        @if(!$selectedSensorId && !$startDate && !$endDate)
        setInterval(function() {
            location.reload();
        }, 60000);
        @endif
    });
</script>
@endsection