@extends('layouts.app')
@section('title', 'Truy cập phòng')
@section('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        }

        .transition {
            transition: all .3s ease;
        }

        .room-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .room-card:hover {
            border-color: #0d6efd;
        }
        
        .sensor-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: #fff;
            border: none;
            margin-bottom: 20px;
            padding: 10px;
            margin-left: 10px;
        }

        .sensor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .sensor-title {
            font-weight: 600;
            color:rgb(47, 0, 255);
            margin-bottom: 1px;
            font-size: 16px;
        }

        .sensor-unit {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 10px;
            padding-left: 5px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;

        }

        .no-data-message {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .title_tab {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            padding: 10px;
        }


    /* Xóa các CSS trùng lặp và thay thế bằng CSS mới */
    .sensor-card-value {
        transition: all 0.3s ease;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        background-color: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 15px;
        padding: 0;
    }

    .sensor-card-value:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 12px rgba(0,0,0,0.12);
    }

    .sensor-card-value .card-body {
        padding: 15px;
    }

    .sensor-title-value {
        font-weight: 600;
        color: #4361ee;
        margin-bottom: 0;
        font-size: 14px;
    }

    .sensor-unit-value {
        color: #6c757d;
        font-size: 12px;
        margin-bottom: 8px;
    }

    #card_value .badge {
        font-size: 12px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
    }

    #card_value .bg-success {
        background-color: #2ecc71 !important;
    }

    #card_value .bg-danger {
        background-color: #e74c3c !important;
    }

    #card_value .bg-light {
        background-color: #f8f9fa !important;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,0.03);
    }

    #card_value .text-primary {
        color: #3498db !important;
        font-size: 22px;
        font-weight: 600;
    }

    #card_value .text-info {
        color: #00bcd4 !important;
        font-size: 15px;
        font-weight: 600;
    }

    #card_value .text-warning {
        color: #f39c12 !important;
        font-size: 15px;
        font-weight: 600;
    }

    #card_value .text-danger {
        color: #e74c3c !important;
        font-size: 15px;
        font-weight: 600;
    }

    #card_value small.text-muted {
        font-size: 11px;
    }

    #card_value .p-3 {
        padding: 10px !important;
    }

    #card_value .p-2 {
        padding: 6px !important;
    }

    #card_value .mt-3.text-muted {
        text-align: right;
        font-style: italic;
        font-size: 11px;
        margin-top: 8px !important;
    }


    </style>
@endsection

@section('content')
@if ($id_phong==0)
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12" style="padding-left: 0;">
                <div class="ibox float-e-margins">
                    <div class="ibox-title my-ibox-title">
                        <h2>Danh sách phòng máy</h2>
                    </div>
                    <div class="ibox-content">
                        @if (count($rooms) == 0)
                            <div class="no-data-message">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>Không có dữ liệu phòng máy nào được tìm thấy.</p>
                            </div>
                        @else
                        <div class="row">
                            @foreach ($rooms as $room)
                                @php
                                    if ($khoaphong->contains('id_phong', $room->id)) {
                                        $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                        $timebegin = $khoaphong->first()->thoi_gian_bat_dau->format('Y-m-d H:i');
                                        $timeend = $khoaphong->first()->thoi_gian_ket_thuc->format('Y-m-d H:i');
                                        $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                        $status = '';
                                        $statusClass = '';
                                        
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
                                        $isLocked = true;
                                    } else {
                                        $status = 'Không bị khóa';
                                        $statusClass = 'badge-secondary';
                                        $isLocked = false;
                                    }
                                @endphp
                                <div class="col-md-3" @if(!$isLocked) onclick="accessRoom('{{ $room->id }}', '{{ $room->ten_phong }}', '{{ $room->khu_vuc }}', '{{ $room->vi_tri }}')" @endif>
                                    <a href="" 
                                        class="text-decoration-none @if($isLocked) disabled @endif"
                                        data-id="{{ $room->id }}"
                                        @if(!$isLocked)
                                        data-toggle="modal"
                                        data-target="#accessModal"
                                        @endif>
                                        <div class="card h-100 room-card hover-shadow transition @if($isLocked) bg-light @endif">
                                            <div class="card-body text-center p-4">
                                                <i class="fas @if($isLocked) fa-door-closed @else fa-door-open @endif fa-3x @if($isLocked) text-muted @else text-primary @endif mb-3"></i>
                                                <h4 class="card-title @if($isLocked) text-muted @else text-dark @endif">{{ $room->ten_phong }}</h4>
                                                <p class="card-text text-muted">
                                                    {{ $room->khu_vuc }} - {{ $room->vi_tri }}
                                                </p>
                                                <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>                         
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12" style="padding-left: 0;">
                <div class="ibox float-e-margins">
                    <div class="ibox-title my-ibox-title">
                        @php
                            $roomcomputer = $rooms->where('id', $id_phong)->first();
                        @endphp
                        <h2>
                            Phòng máy tính {{ $roomcomputer->ten_phong }} - {{ $roomcomputer->khu_vuc }} - {{ $roomcomputer->vi_tri }}
                        </h2>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" data-toggle="modal" data-target="#closeRoomModal" onclick="closeroom('{{ $id_phong }}')">
                                <i class="fas fa-door-closed"></i> Đóng phòng
                            </button>
                            <script>
                                function closeroom(id) {
                                    document.getElementById('closeRoomId').value = id;
                                    document.getElementById('closeRoomName').textContent = 'Phòng máy tính {{ $roomcomputer->ten_phong }} - {{ $roomcomputer->khu_vuc }} - {{ $roomcomputer->vi_tri }}';
                                }
                            </script>
                            <span id="countdown" class="float-right" style="font-size: 16px; color: #6c757d;"></span>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <div class="row mt-4">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body"  >
                                            <div class="ibox-title my-ibox-title">
                                                <h4 class="title_tab mb-4">
                                                    Dữ liệu thu thập hiện tại
                                                </h4>
                                                <div class="d-flex gap-2">
                                                    <button id="toggleDataBtn" class="btn btn-primary" onclick="toggleDataVisibility()">
                                                        <i class="fas fa-eye" id="icondata"></i><span id="showdata"> Hiện dữ liệu</span> 
                                                    </button>

                                                    <script>
                                                        function toggleDataVisibility() {
                                                            const chartContainer = document.getElementById('card_value');
                                                            const iconElement = document.getElementById('icondata');
                                                            const textElement = document.getElementById('showdata');
                                                            
                                                            if (chartContainer.style.display === 'none') {
                                                                chartContainer.style.display = 'block';
                                                                textElement.textContent = ' Ẩn dữ liệu';
                                                                iconElement.classList.remove('fa-eye');
                                                                iconElement.classList.add('fa-eye-slash');
                                                            } else {
                                                                chartContainer.style.display = 'none';
                                                                textElement.textContent = ' Hiện dữ liệu';
                                                                iconElement.classList.remove('fa-eye-slash');
                                                                iconElement.classList.add('fa-eye');
                                                            }
                                                        }
                                                    </script>
                                                </div>
                                            </div>

                                            
                                            <div class="row mx-2" id="card_value" style="width: 100%; align-items: center">
                                                @if(isset($datatime) && count($datatime) > 0)
                                                <div class="row" style="align-items: center; margin: 0px 10px 0px 10px; padding: 20px;  width: 100%">
                                                    @foreach($datatime as $index => $sensor)
                                                        <div class="col-lg-4 col-md-6 mb-3">
                                                            <div class="card sensor-card-value">
                                                                <div class="card-body">
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <h5 class="sensor-title-value">{{ $sensor['title'] }}</h5>
                                                                        <span style="font-size: 12px" class="badge {{ $sensor['sensor_status'] == 'normal' ? 'bg-success' : 'bg-danger' }} text-white small">
                                                                            {{ $sensor['sensor_status'] }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="sensor-unit-value">Đơn vị: {{ $sensor['storageunits'] ?? '' }}</div>
                                                                    
                                                                    <div class="row mt-2">
                                                                        <div class="col-12 mb-2">
                                                                            <div class="p-3 bg-light rounded text-center">
                                                                                <small class="text-muted d-block mb-1">Giá trị hiện tại</small>
                                                                                <h4 class="mb-0 text-primary">{{ $sensor['current_value'] }}</h4>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-4 px-1">
                                                                            <div class="p-2 bg-light rounded text-center">
                                                                                <small class="text-muted d-block">Thấp nhất</small>
                                                                                <span class="text-info">{{ $sensor['min'] }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-4 px-1">
                                                                            <div class="p-2 bg-light rounded text-center">
                                                                                <small class="text-muted d-block">Trung bình</small>
                                                                                <span class="text-warning">{{ number_format($sensor['avg'], 2) }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-4 px-1">
                                                                            <div class="p-2 bg-light rounded text-center">
                                                                                <small class="text-muted d-block">Cao nhất</small>
                                                                                <span class="text-danger">{{ $sensor['max'] }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mt-3 text-muted" style="font-size: 14px;">
                                                                        <i class="fas fa-clock mr-1"></i> {{ $sensor['time'] }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @else
                                                <div class="no-data-message" style="margin-left: 30px; margin-bottom: 15px">
                                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                    <p>Không có dữ liệu cảm biến nào được tìm thấy cho phòng này.</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body"  >
                                            <div class="ibox-title my-ibox-title">
                                                <h4 class="title_tab mb-4">
                                                    Thiết bị phòng máy
                                                </h4>
                                                <div class="d-flex gap-2">
                                                    <button id="toggleDeviceBtn" class="btn btn-primary" onclick="toggleDeviceVisibility()">
                                                        <i class="fas fa-eye" id="icondevice"></i><span id="showdevice"> Hiện dữ liệu</span> 
                                                    </button>
                                                    
                                                    <script>
                                                        function toggleDeviceVisibility() {
                                                            const deviceContainer = document.getElementById('device_container');
                                                            const iconElement = document.getElementById('icondevice');
                                                            const textElement = document.getElementById('showdevice');
                                                            
                                                            if (deviceContainer.style.display === 'none') {
                                                                deviceContainer.style.display = 'block';
                                                                textElement.textContent = ' Ẩn dữ liệu';
                                                                iconElement.classList.remove('fa-eye');
                                                                iconElement.classList.add('fa-eye-slash');
                                                            } else {
                                                                deviceContainer.style.display = 'none';
                                                                textElement.textContent = ' Hiện dữ liệu';
                                                                iconElement.classList.remove('fa-eye-slash');
                                                                iconElement.classList.add('fa-eye');
                                                            }
                                                        }
                                                    </script>
                                                </div>
                                            </div>

                                            
                                            <div class="ibox-content">
                                                @if (count($devices) == 0)
                                                <div class="no-data-message">
                                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                    <p>Không có thiết bị nào được tìm thấy cho phòng này.</p>
                                                </div>
                                                @else
                                                <div class="row">
                                                    @foreach ($devices as $device)
                                                        @php
                                                            if ($khoathietbi->contains('id_thietbi', $device->id)) {
                                                                $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                                                $timebegin = $khoathietbi->first()->thoi_gian_bat_dau->format('Y-m-d H:i');
                                                                $timeend = $khoathietbi->first()->thoi_gian_ket_thuc->format('Y-m-d H:i');
                                                                $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                                                                $status = '';
                                                                $statusClass = '';
                                                                
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
                                                                $isLocked = true;
                                                            } else {
                                                                $status = 'Không bị khóa';
                                                                $statusClass = 'badge-secondary';
                                                                $isLocked = false;
                                                            }
                                                        @endphp
                                                        <div class="col-md-3" @if(!$isLocked) onclick="controlDevece('{{ $device->id }}', '{{ $device->ten_thiet_bi }}', '{{ $device->ma_so }}', '{{ $device->status-> ten_trang_thai }}', '{{ $device->nguong_dieu_khien }}')" @endif>
                                                            <a href="" 
                                                                class="text-decoration-none @if($isLocked) disabled @endif"
                                                                data-id="{{ $device->id  }}"
                                                                @if(!$isLocked)
                                                                data-toggle="modal"
                                                                data-target="#controlModal"
                                                                @endif>
                                                                <div class="card h-100 room-card hover-shadow transition @if($isLocked) bg-light @endif">
                                                                    <div class="card-body text-center p-4">
                                                                        <i class="fas @if($isLocked) fa-laptop-code @else fa-laptop @endif fa-3x @if($isLocked) text-muted @else text-primary @endif mb-3"></i>
                                                                        <h4 class="card-title @if($isLocked) text-muted @else text-dark @endif">{{ $device->ten_thiet_bi }} <br> {{ $device->status-> ten_trang_thai}}</h4>
                                                                        <p class="card-text text-muted">
                                                                            Ngưỡng điều khiển: {{ $device->nguong_dieu_khien ?? 'NULL' }}
                                                                        </p>
                                                                        <p class="card-text text-muted">
                                                                            Mã số: {{ $device->ma_so }}
                                                                        </p>
                                                                        <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="ibox-title my-ibox-title">
                                                <h4 class="title_tab mb-4">
                                                    Dữ liệu cảm biến trong ngày
                                                </h4>
                                                <div class="d-flex gap-2">
                                                    <button id="toggleChartBtn" class="btn btn-primary" onclick="toggleChartVisibility()">
                                                        <i class="fas fa-eye" id="iconchart"></i><span id="showchart"> Hiện dữ liệu</span> 
                                                    </button>

                                                    <script>
                                                        function toggleChartVisibility() {
                                                            const chartContainer = document.getElementById('card_chart');
                                                            if (chartContainer.style.display == 'none') {
                                                                chartContainer.style.display = 'block';
                                                                document.getElementById('showchart').textContent = ' Ẩn dữ liệu';
                                                                document.getElementById('iconchart').classList.remove('fa-eye');
                                                                document.getElementById('iconchart').classList.add('fa-eye-slash');
                                                            } else {
                                                                chartContainer.style.display = 'none';
                                                                document.getElementById('showchart').textContent = ' Hiện dữ liệu';
                                                                document.getElementById('iconchart').classList.remove('fa-eye-slash');
                                                                document.getElementById('iconchart').classList.add('fa-eye');
                                                            }
                                                        }
                                                    </script>
                                                </div>
                                            </div>

                                            
                                            <div class="row mx-2" id="card_chart" style="display: none; width: 100%; align-items: center">
                                                @if(isset($datalist) && count($datalist) > 0)
                                                <div class="row" style="align-items: center; margin: 0px 10px 0px 10px; padding: 20px;  width: 100%">
                                                    @foreach($datalist as $index => $sensor)
                                                        <div class="col-lg-6">
                                                            <div class="card sensor-card">
                                                                <div class="card-body">
                                                                    <h5 class="sensor-title">{{ $sensor['title'] }}</h5>
                                                                    <div class="sensor-unit">Đơn vị: {{ $sensor['storageunits'] ?? '' }}</div>
                                                                    <div class="chart-container">
                                                                        <canvas id="sensorChart{{ $index }}"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @else
                                                <div class="no-data-message" style="margin-left: 30px;">
                                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                                    <p>Không có dữ liệu cảm biến nào được tìm thấy cho phòng này.</p>
                                                </div>
                                                @endif
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
@endif

@endsection
@include('access-room.partials.modals')
@section('js')
<script>
    function accessRoom(roomId, roomName, roomLocation, roomPosition) {
        document.getElementById('roomId').value = roomId;
        document.getElementById('roomName').textContent = roomName +' - '+ roomLocation+' - '+roomPosition;
    }

    function controlDevece(deviceId, deviceName, deviceCode, deviceStatus, deviceThreshold) {
        document.getElementById('deviceId').value = deviceId;
        document.getElementById('deviceThreshold').value = parseFloat(deviceThreshold).toFixed(2);
        document.getElementById('namedevices').textContent = deviceName +' - '+ deviceCode+' - '+deviceStatus;
        if (deviceStatus == 'Hoạt động') {
            document.getElementById('turnOnBtn').disabled = true;
            document.getElementById('turnOffBtn').disabled = false;
        } else {
            document.getElementById('turnOnBtn').disabled = false; 
            document.getElementById('turnOffBtn').disabled = true;
        }

        document.getElementById('deviceStatus').textContent = deviceStatus;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sensorData = @json($datalist ?? []);
    
    sensorData.forEach((sensor, index) => {
        createSensorChart(sensor, index);
    });
    
    function createSensorChart(sensorInfo, index) {
        const ctx = document.getElementById(`sensorChart${index}`).getContext('2d');
        
        // Xác định màu sắc dựa trên loại cảm biến
        let chartColor, chartGradient;
        
        // Màu xanh lá cho cảm biến ánh sáng
        if (sensorInfo.title.toLowerCase().includes('ánh sáng')) {
            chartColor = '#1cc88a';
            chartGradient = ctx.createLinearGradient(0, 0, 0, 400);
            chartGradient.addColorStop(0, 'rgba(28, 200, 138, 0.4)');
            chartGradient.addColorStop(1, 'rgba(28, 200, 138, 0.05)');
        } 
        // Màu xanh dương cho cảm biến nhiệt độ
        else if (sensorInfo.title.toLowerCase().includes('dht')) {
            chartColor = '#36b9cc';
            chartGradient = ctx.createLinearGradient(0, 0, 0, 400);
            chartGradient.addColorStop(0, 'rgba(54, 185, 204, 0.4)');
            chartGradient.addColorStop(1, 'rgba(54, 185, 204, 0.05)');
        }
        // Màu mặc định
        else {
            chartColor = '#4e73df';
            chartGradient = ctx.createLinearGradient(0, 0, 0, 400);
            chartGradient.addColorStop(0, 'rgba(78, 115, 223, 0.4)');
            chartGradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)');
        }
        
        // Chuẩn bị dữ liệu cho biểu đồ
        const chartData = {
            labels: sensorInfo.data.map(item => item.time),
            datasets: [{
                label: sensorInfo.title,
                data: sensorInfo.data.map(item => item.value),
                backgroundColor: chartGradient,
                borderColor: chartColor,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: chartColor,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: chartColor,
                pointHoverBorderColor: '#fff',
                tension: 0.4,
                fill: true
            }]
        };
        
        // Cấu hình biểu đồ
        const config = {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#666'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            font: {
                                size: 11
                            },
                            color: '#666',
                            maxTicksLimit: 10
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        };
        
        // Tạo biểu đồ
        new Chart(ctx, config);
    }
    
    // Thêm chức năng tự động tải lại trang sau 60 giây
    if (document.getElementById('countdown')) {
        let countdownTime = 10;
        
        function updateCountdown() {
            document.getElementById('countdown').textContent = `Làm mới sau: ${countdownTime}s`;
            countdownTime--;
            
            if (countdownTime < 0) {
                location.reload();
            }
        }
        
        // Cập nhật đồng hồ đếm ngược mỗi giây
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});
</script>
@endsection