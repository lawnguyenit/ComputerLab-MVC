@extends('layouts.app')
@section('title', 'Dự báo thời tiết Vĩnh Long')

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title my-ibox-title d-flex justify-content-between align-items-center">
                    <h2><i class="fa fa-cloud me-2"></i>Dự báo thời tiết Vĩnh Long</h2>
                    <div>
                        <span class="text-muted">Cập nhật lần cuối: {{ date('H:i d/m/Y', strtotime('+7 hours')) }}</span>
                        <span id="countdown" class="badge bg-secondary ms-2">60s</span>
                    </div>
                </div>
                <div class="ibox-content">
                    @if(isset($error))
                        <div class="alert alert-danger">
                            {{ $error }}
                        </div>
                    @else
                        <!-- Thời tiết hiện tại -->
                        <div class="row mb-4" >
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h3 class="mb-0">Thời tiết hiện tại</h3>
                                            <span class="badge bg-primary">{{ $currentWeather['name'] }}</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-3"  style="display: flex; justify-content: space-evenly; align-items: flex-end;">
                                            <div class="text-center me-4">
                                                <img src="https://openweathermap.org/img/wn/{{ $currentWeather['weather'][0]['icon'] }}@2x.png" alt="{{ $currentWeather['weather'][0]['description'] }}" class="img-fluid" style="width: 100px;">
                                                <p class="text-capitalize mb-0">{{ $currentWeather['weather'][0]['description'] }}</p>
                                            </div>
                                            <div style="align-items: center">
                                                <h1 class="display-4 mb-0 fw-bold" style="font-size: 50px;">{{ round($currentWeather['main']['temp']) }}°C</h1>
                                                <p class="text-muted">Cảm giác như: {{ round($currentWeather['main']['feels_like']) }}°C</p>
                                            </div>
                                        </div>
                                        <br>
                                        <br>
                                        <div class="row mt-4" style="display: flex; justify-content: space-evenly;">
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <i class="fa fa-gauge-high fa-lg text-warning mb-2"></i>
                                                    <p class="mb-0 text-muted small">Áp suất</p>
                                                    <h5>{{ round($currentWeather['main']['pressure']) }} hPa</h5>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <i class="fa fa-temperature-high fa-lg text-danger mb-2"></i>
                                                    <p class="mb-0 text-muted small">Cao nhất</p>
                                                    <h5>{{ round($currentWeather['main']['temp_max']) }}°C</h5>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <i class="fa fa-temperature-low fa-lg text-success mb-2"></i>
                                                    <p class="mb-0 text-muted small">Thấp nhất</p>
                                                    <h5>{{ round($currentWeather['main']['temp_min']) }}°C</h5>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <i class="fa fa-droplet fa-lg text-info mb-2"></i>
                                                    <p class="mb-0 text-muted small">Độ ẩm</p>
                                                    <h5>{{ $currentWeather['main']['humidity'] }}%</h5>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <i class="fa fa-wind fa-lg text-secondary mb-2"></i>
                                                    <p class="mb-0 text-muted small">Gió</p>
                                                    <h5>{{ round($currentWeather['wind']['speed'] * 3.6) }} km/h</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-lg h-100">
                                    <div class="card-body p-4">
                                        <h3 class="mb-3">Dự báo hôm nay và ngày mai</h3>
                                        <div class="daily-forecast">
                                            @foreach($dailyForecast as $day)
                                            <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3" style="width: 80px;">
                                                        <strong>{{ $day['day_name'] }}</strong><br>
                                                        <small class="text-muted">{{ $day['date'] }}</small>
                                                    </div>
                                                    <img src="https://openweathermap.org/img/wn/{{ $day['icon'] }}.png" alt="{{ $day['description'] }}" width="40">
                                                </div>
                                                <div class="text-end">
                                                    <span class="text-danger me-2">{{ round($day['temp_max']) }}°</span>
                                                    <span class="text-primary">{{ round($day['temp_min']) }}°</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-3 text-center">
                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <div>
                                                    <i class="fa fa-sun fa-lg text-warning me-2"></i>
                                                    <span>Bình minh: {{ date('H:i', $currentWeather['sys']['sunrise'] + 7*3600) }}</span>
                                                </div>
                                                <br>
                                                <div>
                                                    <i class="fa fa-moon fa-lg text-primary me-2"></i>
                                                    <span>Hoàng hôn: {{ date('H:i', $currentWeather['sys']['sunset'] + 7*3600) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Biểu đồ dự báo -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm rounded-lg">
                                    <div class="card-body p-4">
                                        <h3 class="mb-3">Biểu đồ dự báo 24 giờ tới</h3>
                                        <canvas id="weatherChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dự báo chi tiết -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm rounded-lg">
                                    <div class="card-body p-4">
                                        <h3 class="mb-3">Dự báo chi tiết theo giờ</h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Thời gian</th>
                                                        <th>Nhiệt độ</th>
                                                        <th>Độ ẩm</th>
                                                        <th>Mô tả</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($forecastData as $item)
                                                    <tr>
                                                        <td>{{ $item['time'] }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="https://openweathermap.org/img/wn/{{ $item['icon'] }}.png" alt="{{ $item['description'] }}" class="me-2">
                                                                {{ $item['temp'] }}°C
                                                            </div>
                                                        </td>
                                                        <td>{{ $item['humidity'] }}%</td>
                                                        <td class="text-capitalize">{{ $item['description'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Thêm Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dữ liệu cho biểu đồ
        var labels = {!! $labels ?? '[]' !!};
        var temperatures = {!! $temperatures ?? '[]' !!};
        var humidity = {!! $humidity ?? '[]' !!};
        
        // Create chart
        var ctx = document.getElementById('weatherChart').getContext('2d');
        var weatherChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nhiệt độ (°C)',
                        data: temperatures,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y',
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        pointRadius: 3
                    },
                    {
                        label: 'Độ ẩm (%)', 
                        data: humidity,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1',
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + (context.datasetIndex === 0 ? '°C' : '%');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nhiệt độ (°C)',
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Độ ẩm (%)',
                            font: {
                                size: 10
                            }
                        },
                        min: 0,
                        max: 100,
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Auto refresh countdown
        const countdownElement = document.getElementById('countdown');
        let secondsLeft = 60;

        function updateCountdown() {
            if (!countdownElement) return;
            
            secondsLeft--;
            countdownElement.textContent = secondsLeft + 's';
            
            if (secondsLeft <= 0) {
                window.location.reload();
                return;
            }

            // Update badge color based on remaining time
            if (secondsLeft <= 10) {
                countdownElement.className = 'badge bg-danger ms-2';
            } else if (secondsLeft <= 30) {
                countdownElement.className = 'badge bg-warning text-dark ms-2';
            }
            
            setTimeout(updateCountdown, 1000);
        }
        
        // Start countdown
        setTimeout(updateCountdown, 1000);
    });
</script>

<!-- Thêm Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .card {
        transition: all 0.2s ease;
        border-radius: 8px;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
    }
    
    .daily-forecast > div:last-child {
        border-bottom: none !important;
    }
    
    .daily-forecast > div {
        transition: all 0.2s ease;
    }
    
    .daily-forecast > div:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    /* Thu nhỏ kích thước chung */
    h4 {
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .card-body {
        padding: 0.75rem !important;
    }
    
    /* Đảm bảo biểu đồ không quá lớn */
    #weatherChart {
        max-height: 250px !important;
    }
    
    /* Thu nhỏ bảng dự báo */
    .table-sm td, .table-sm th {
        padding: 0.3rem 0.5rem;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        h2 {
            font-size: 1.5rem;
        }
    }
    
    /* Hiệu ứng cho đồng hồ đếm ngược */
    #countdown {
        transition: all 0.3s ease;
        min-width: 40px;
    }
</style>
@endsection