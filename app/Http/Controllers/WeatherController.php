<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function index()
    {
        // API key từ OpenWeatherMap (bạn cần đăng ký tài khoản miễn phí để lấy API key)
        $apiKey = '515d938f6377f213cae7497e10e35cbd';
        
        // Tọa độ của Vĩnh Long
        $lat = '10.2537';
        $lon = '105.9722';
        
        try {
            // Lấy dữ liệu thời tiết hiện tại
            $currentWeather = Http::withOptions([
                'verify' => false, // Tắt xác minh SSL
            ])->get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric', // Đơn vị đo là Celsius
                'lang' => 'vi' // Ngôn ngữ tiếng Việt
            ])->json();
            
            // Lấy dữ liệu dự báo 5 ngày (dữ liệu 3 giờ một lần)
            $forecast = Http::withOptions([
                'verify' => false, // Tắt xác minh SSL
            ])->get("https://api.openweathermap.org/data/2.5/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'vi',
                'cnt' => 32 // Get 4 days of forecast data (8 data points per day)
            ])->json();
            
            // Initialize arrays for chart data
            $forecastData = [];
            $labels = [];
            $temperatures = [];
            $humidity = [];
            $dailyForecast = [];
            $currentDay = '';
            
            // Check if forecast data exists
            if (!isset($forecast['list']) || empty($forecast['list'])) {
                throw new \Exception('No forecast data available');
            }
            
            // Lấy dữ liệu cho biểu đồ (giới hạn 8 điểm dữ liệu - 24 giờ)
            $count = 0;
            foreach ($forecast['list'] as $item) {
                if ($count >= 8) break;
                
                $date = date('d/m H:i', $item['dt']);
                $day = date('d/m', $item['dt']);
                $hour = date('H:i', $item['dt']);
                
                $labels[] = $hour;
                $temperatures[] = round($item['main']['temp'], 1);
                $humidity[] = $item['main']['humidity'];
                
                $forecastData[] = [
                    'time' => $date,
                    'temp' => round($item['main']['temp'], 1),
                    'humidity' => $item['main']['humidity'],
                    'description' => $item['weather'][0]['description'],
                    'icon' => $item['weather'][0]['icon']
                ];
                
                // Tạo dự báo theo ngày
                if ($day != $currentDay) {
                    $currentDay = $day;
                    $dailyForecast[$day] = [
                        'date' => $day,
                        'day_name' => $this->getDayName(date('w', $item['dt'])),
                        'icon' => $item['weather'][0]['icon'],
                        'description' => $item['weather'][0]['description'],
                        'temp_min' => $item['main']['temp_min'],
                        'temp_max' => $item['main']['temp_max'],
                        'humidity' => $item['main']['humidity']
                    ];
                } else {
                    // Cập nhật nhiệt độ min/max cho ngày
                    if ($item['main']['temp_min'] < $dailyForecast[$day]['temp_min']) {
                        $dailyForecast[$day]['temp_min'] = $item['main']['temp_min'];
                    }
                    if ($item['main']['temp_max'] > $dailyForecast[$day]['temp_max']) {
                        $dailyForecast[$day]['temp_max'] = $item['main']['temp_max'];
                    }
                }
                
                $count++;
            }
            
            // Ensure arrays are not empty before encoding
            $encodedLabels = !empty($labels) ? json_encode($labels) : json_encode([]);
            $encodedTemperatures = !empty($temperatures) ? json_encode($temperatures) : json_encode([]);
            $encodedHumidity = !empty($humidity) ? json_encode($humidity) : json_encode([]);
            
            return view('weather.index', [
                'currentWeather' => $currentWeather,
                'forecastData' => $forecastData,
                'dailyForecast' => array_values($dailyForecast),
                'labels' => $encodedLabels,
                'temperatures' => $encodedTemperatures,
                'humidity' => $encodedHumidity
            ]);
            
        } catch (\Exception $e) {
            return view('weather.index', [
                'error' => 'Không thể lấy dữ liệu thời tiết. Vui lòng thử lại sau. Lỗi: ' . $e->getMessage()
            ]);
        }
    }
    
    private function getDayName($dayNumber) {
        $days = [
            'Chủ nhật',
            'Thứ hai',
            'Thứ ba', 
            'Thứ tư',
            'Thứ năm',
            'Thứ sáu',
            'Thứ bảy'
        ];
        
        return $days[$dayNumber];
    }
}