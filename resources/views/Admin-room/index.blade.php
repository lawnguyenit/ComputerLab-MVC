@extends('layouts.app')
@section('title', 'Quản trị phòng máy tính')

@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12" style="padding-left: 0;">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title">
                    <h2>Quản trị phòng máy tính</h2>
                </div>
                <div class="card">
                    <div class="card-body"  >
                        <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                            <h2 class="title_tab mb-0">
                                Thiết bị phòng máy
                            </h2>
                            <div class="d-flex gap-2">
                                <button 
                                    id="toggleDeviceBtn" 
                                    class="btn btn-primary d-flex align-items-center gap-2"
                                    onclick="toggleDeviceVisibilityDevices()"
                                >
                                    <i class="fas fa-eye" id="icondevice"></i>
                                    <span id="showdevice">Hiện dữ liệu</span>
                                </button>
                            </div>
                            <script>
                                function toggleDeviceVisibilityDevices() {
                                    var formdevices = document.getElementById('formdevices');
                                    var showdevice = document.getElementById('showdevice');
                                    var icondevice = document.getElementById('icondevice');

                                    if (formdevices.style.display === 'none') {
                                        formdevices.style.display = 'block';
                                        showdevice.textContent = 'Ẩn dữ liệu';
                                        icondevice.classList.remove('fa-eye');
                                        icondevice.classList.add('fa-eye-slash');   
                                    } else {
                                        formdevices.style.display = 'none';
                                        showdevice.textContent = 'Hiện dữ liệu';
                                        icondevice.classList.remove('fa-eye-slash');
                                        icondevice.classList.add('fa-eye');
                                    }
                                }
                            </script>
                        </div>
                        <div class="ibox-content" id="formdevices" name="formdevices" style="display: none;">
                            @if (count($devices) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có thiết bị nào được tìm thấy cho phòng này.</p>
                            </div>
                            @else
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered table-hover dataTables-devices">
                                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên thiết bị</th>
                                            <th>Mã số</th>
                                            <th>Loại thiết bị</th>
                                            <th>Phòng</th>
                                            <th>Ngưỡng điều khiển</th>
                                            <th>Trạng thái</th>
                                            <th>Khóa thiết bị</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($devices as $device)
                                        <tr class="gradeX">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $device->ten_thiet_bi }}</td>
                                            <td>{{ $device->ma_so }}</td>
                                            <td>{{ $device->deviceType->ten_loai_thiet_bi }}</td>
                                            <td>{{ $device->room->ten_phong }}</td>
                                            <td>{{ $device->nguong_dieu_khien ?? 'NULL' }}</td>
                                            <td>
                                                <span class="badge {{ $device->status->id == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $device->status->ten_trang_thai }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    if ($khoathietbi->contains('id_thietbi', $device->id)) {
                                                        $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                                        $timebegin = $khoathietbi->first()->thoi_gian_bat_dau->format('Y-m-d H:i');
                                                        $timeend = $khoathietbi->first()->thoi_gian_ket_thuc->format('Y-m-d H:i');
                                                        
                                                        if ($now < $timebegin) {
                                                            $status = 'Chưa đến';
                                                            $statusClass = 'badge-warning';
                                                        } elseif ($now > $timeend) {
                                                            $status = 'Đã kết thúc';
                                                            $statusClass = 'badge-danger';
                                                        } else {
                                                            $status = 'Đang bị khóa';
                                                            $statusClass = 'badge-success';
                                                        }
                                                    } else {
                                                        $status = 'Không khóa';
                                                        $statusClass = 'badge-secondary';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                            </td>
                                            
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                            <h2 class="title_tab mb-0">
                                Cảm biến phòng máy
                            </h2>
                            <div class="d-flex gap-2">
                                <button 
                                    id="toggleDeviceBtn" 
                                    class="btn btn-primary d-flex align-items-center gap-2"
                                    onclick="toggleDeviceVisibilitySensor()"
                                >
                                    <i class="fas fa-eye" id="iconsensor"></i>
                                    <span id="showsensor">Hiện dữ liệu</span>
                                </button>
                            </div>
                            <script>
                                function toggleDeviceVisibilitySensor() {
                                    var formdevices = document.getElementById('formsensor');
                                    var showdevice = document.getElementById('showsensor');
                                    var icondevice = document.getElementById('iconsensor');

                                    if (formdevices.style.display === 'none') {
                                        formdevices.style.display = 'block';
                                        showdevice.textContent = 'Ẩn dữ liệu';
                                        icondevice.classList.remove('fa-eye');
                                        icondevice.classList.add('fa-eye-slash');   
                                    } else {
                                        formdevices.style.display = 'none';
                                        showdevice.textContent = 'Hiện dữ liệu';
                                        icondevice.classList.remove('fa-eye-slash');
                                        icondevice.classList.add('fa-eye');
                                    }
                                }
                            </script>
                        </div>
                        <div class="ibox-content" id="formsensor" name="formsensor" style="display: none;">
                            @if (count($sensors) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có cảm biến nào được tìm thấy cho phòng này.</p>
                            </div>
                            @else
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered table-hover dataTables-devices">
                                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                        <tr>
                                            <th>STT</th>
                                            <th>Tên cảm biến</th>
                                            <th>Mã số</th>
                                            <th>Loại cảm biến</th>
                                            <th>Phòng</th>
                                            <th>Giá trị hiện tại</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sensors as $sensor)
                                        <tr class="gradeX">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $sensor->ten_cam_bien }}</td>
                                            <td>{{ $sensor->ma_so }}</td>
                                            <td>{{ $sensor->sensorType->ten_loai_cam_bien }}</td>
                                            <td>{{ $sensor->room->ten_phong }}</td>
                                            <td>{{ isset($list[$sensor->id]) ? $list[$sensor->id] : 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $sensor->status->id == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $sensor->status->ten_trang_thai }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                            <h2 class="title_tab mb-0">
                                Truy cập phòng máy
                            </h2>
                            <div class="d-flex gap-2">
                                <button 
                                    id="toggleDeviceBtn" 
                                    class="btn btn-primary d-flex align-items-center gap-2"
                                    onclick="toggleDeviceVisibilityRoom()"
                                >
                                    <i class="fas fa-eye" id="iconroom"></i>
                                    <span id="showroom">Hiện dữ liệu</span>
                                </button>
                            </div>
                            <script>
                                function toggleDeviceVisibilityRoom() {
                                    var formdevices = document.getElementById('formroom');
                                    var showdevice = document.getElementById('showroom');
                                    var icondevice = document.getElementById('iconroom');

                                    if (formdevices.style.display === 'none') {
                                        formdevices.style.display = 'block';
                                        showdevice.textContent = 'Ẩn dữ liệu';
                                        icondevice.classList.remove('fa-eye');
                                        icondevice.classList.add('fa-eye-slash');   
                                    } else {
                                        formdevices.style.display = 'none';
                                        showdevice.textContent = 'Hiện dữ liệu';
                                        icondevice.classList.remove('fa-eye-slash');
                                        icondevice.classList.add('fa-eye');
                                    }
                                }
                            </script>
                        </div>
                        <div class="ibox-content" id="formroom" name="formroom" style="display: none;">
                            @if (count($accessroom) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có lượt truy cập phòng nào được tìm thấy cho phòng này.</p>
                            </div>
                            @else
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered table-hover dataTables-devices">
                                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                        <tr>
                                            <th>STT</th>
                                            <th>Họ tên</th>
                                            <th>Email</th>
                                            <th>SĐT</th>
                                            <th>Vào phòng</th>
                                            <th>Thoát khỏi phòng</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accessroom as $access)
                                        <tr class="gradeX">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $access -> user -> ho_ten }}</td>
                                            <td>{{ $access -> user -> email }}</td>
                                            <td>{{ $access -> user -> sdt }}</td>
                                            <td>{{ $access -> thoi_gian_bat_dau }}</td>
                                            <td>{{ $access->thoi_gian_ket_thuc ?? 'Chưa rời phòng' }}</td>
                                            <td>
                                                <span class="badge {{ $access->usageStatus->id == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $access->usageStatus->ten_trang_thai }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                            <h2 class="title_tab mb-0">
                                Sử dụng thiết bị
                            </h2>
                            <div class="d-flex gap-2">
                                <button 
                                    id="toggleDeviceBtn" 
                                    class="btn btn-primary d-flex align-items-center gap-2"
                                    onclick="toggleDeviceVisibilityDevice()"
                                >
                                    <i class="fas fa-eye" id="icondevice"></i>
                                    <span id="showdevice">Hiện dữ liệu</span>
                                </button>
                            </div>
                            <script>
                                function toggleDeviceVisibilityDevice() {
                                    var formdevices = document.getElementById('formdevice');
                                    var showdevice = document.getElementById('showdevice');
                                    var icondevice = document.getElementById('icondevice');

                                    if (formdevices.style.display === 'none') {
                                        formdevices.style.display = 'block';
                                        showdevice.textContent = 'Ẩn dữ liệu';
                                        icondevice.classList.remove('fa-eye');
                                        icondevice.classList.add('fa-eye-slash');   
                                    } else {
                                        formdevices.style.display = 'none';
                                        showdevice.textContent = 'Hiện dữ liệu';
                                        icondevice.classList.remove('fa-eye-slash');
                                        icondevice.classList.add('fa-eye');
                                    }
                                }
                            </script>
                        </div>
                        <div class="ibox-content" id="formdevice" name="formdevice" style="display: none;">
                            @if (count($controldevice) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có lượt sử dụng thiết bị nào được tìm thấy cho phòng này.</p>
                            </div>
                            @else
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-bordered table-hover dataTables-devices">
                                    <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                        <tr>
                                            <th>STT</th>
                                            <th>Họ tên</th>
                                            <th>Email</th>
                                            <th>SĐT</th>
                                            <th>Điều khiển</th>
                                            <th>Bật thiết bị</th>
                                            <th>Tắt thiết bị</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($controldevice as $control)
                                        <tr class="gradeX">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $control -> user -> ho_ten }}</td>
                                            <td>{{ $control -> user -> email }}</td>
                                            <td>{{ $control -> user -> sdt }}</td>
                                            <td>{{ $control -> device -> ten_thiet_bi }}</td>
                                            <td>{{ $control -> thoi_gian_bat_dau ?? 'Không bật' }}</td>
                                            <td>{{ $control->thoi_gian_ket_thuc ?? 'Không tắt' }}</td>
                                            <td>
                                                <span class="badge {{ $control->usageStatus->id == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $control->usageStatus->ten_trang_thai }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                            <h2 class="title_tab mb-0">
                                Dữ liệu phòng máy
                            </h2>
                            <div class="d-flex gap-2">
                                <button 
                                    id="toggleDeviceBtn" 
                                    class="btn btn-primary d-flex align-items-center gap-2"
                                    onclick="toggleDeviceVisibilityData()"
                                >
                                    <i class="fas fa-eye" id="icondata"></i>
                                    <span id="showdata">Ẩn dữ liệu</span>
                                </button>
                            </div>
                            <script>
                                function toggleDeviceVisibilityData() {
                                    var formdevices = document.getElementById('formdata');
                                    var showdevice = document.getElementById('showdata');
                                    var icondevice = document.getElementById('icondata');
                          

                                    if (formdevices.style.display === 'none') {
                                        formdevices.style.display = 'block';
                                        showdevice.textContent = 'Ẩn dữ liệu';
                                        icondevice.classList.remove('fa-eye');
                                        icondevice.classList.add('fa-eye-slash');   
                                    } else {
                                        formdevices.style.display = 'none';
                                        showdevice.textContent = 'Hiện dữ liệu';
                                        icondevice.classList.remove('fa-eye-slash');
                                        icondevice.classList.add('fa-eye');
                                    }
                                }
                            </script>
                        </div>
                        <div class="ibox-content" id="formdata" name="formdata">
                            @if (count($sensors) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có dư liệu nào được tìm thấy cho phòng này.</p>
                            </div>
                            @else
                            <div class="room-info mb-4">
                                <div class="card shadow-sm rounded-lg">
                                    <div class="card-header bg-light d-flex align-items-center">
                                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i> Thông tin phòng</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p><strong>Tên phòng:</strong> {{ $room->ten_phong }}</p>
                                                <p><strong>Số lượng thiết bị:</strong> {{ $devices->count() }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Khu vực:</strong> {{ $room->khu_vuc }}</p>
                                                <p><strong>Số cảm biến:</strong> {{ $sensors->count() }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p><strong>Vị trí:</strong> {{ $room->vi_tri }}</p>
                                                <p><strong>Trạng thái:</strong> 
                                                @if($roomusagehistory->contains('id_phong', $room->id))
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
                                            <form action="{{ route('admin-room.index') }}" method="GET" class="form-inline">
                                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                                
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
                                                <a href="{{ route('admin-room.index', ['room_id' => $room->id]) }}" class="btn btn-secondary ml-2">
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
                                    'room_id' => $room->id, 
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
    </div>
</div>
@endsection
@section('js')
<script>
    $(document).ready(function() {
       
    });
</script>
@endsection