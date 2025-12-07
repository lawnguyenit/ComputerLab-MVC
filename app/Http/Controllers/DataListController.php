<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Sensor;
use App\Models\DataList;
use App\Models\Device;
use App\Models\StorageUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RoomUsageHistory;
use Exception;
use Carbon\Carbon;

class DataListController extends Controller
{
    /**
     * Hiển thị danh sách dữ liệu theo phòng
     */
    public function index(Request $request)
    {
        try {
            // Lấy danh sách tất cả các phòng
            $rooms = Room::all();
            
            // Nếu không có phòng nào, trả về view với thông báo
            if ($rooms->isEmpty()) {
                return view('datalist.index', compact('rooms'))
                    ->with('error', 'Không có phòng nào trong hệ thống');
            }
            
            // Lấy ID phòng được chọn từ request, nếu không có thì lấy phòng đầu tiên
            $selectedRoomId = $request->input('room_id', $rooms->first()->id);
            
            // Lấy thông tin phòng được chọn
            $selectedRoom = Room::findOrFail($selectedRoomId);
            
            // Lấy danh sách cảm biến của phòng được chọn
            $sensors = Sensor::where('id_phong', $selectedRoomId)->get();

            $devices = Device::where('id_phong', $selectedRoomId)->get();
            
            // Lấy ID cảm biến được chọn để lọc, nếu không có thì lấy tất cả
            $selectedSensorId = $request->input('sensor_id');
            
            // Lấy khoảng thời gian nếu có
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            // Khởi tạo query để lấy dữ liệu
            $query = DataList::with(['sensor', 'storageUnit'])
                ->byRoom($selectedRoomId);
            
            // Nếu có cảm biến được chọn, lọc theo cảm biến đó
            if ($selectedSensorId) {
                $query->bySensor($selectedSensorId);
            }
            
            // Nếu có khoảng thời gian, lọc theo khoảng thời gian
            if ($startDate && $endDate) {
                // Convert dates to Carbon instances and set time ranges
                $startDateTime = Carbon::parse($startDate)->startOfDay();
                $endDateTime = Carbon::parse($endDate)->endOfDay();
                
                $query->whereBetween('thoi_gian_thu_thap', [$startDateTime, $endDateTime]);
            }
            
            // Lấy dữ liệu, sắp xếp theo thời gian giảm dần và phân trang
            $dataList = $query->orderBy('thoi_gian_thu_thap', 'desc')
                ->paginate(20);
            
            // Lấy danh sách đơn vị lưu trữ để hiển thị
            $storageUnits = StorageUnit::all()->pluck('ten_don_vi', 'id');

            // Get current room usage based on start/end times
            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
            $roomusagehistory = RoomUsageHistory::where('thoi_gian_bat_dau', '<=', $now)
                ->where(function($query) use ($now) {
                    $query->whereNull('thoi_gian_ket_thuc')
                          ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                })
                ->get();
            
            
            return view('datalist.index', compact(
                'rooms', 
                'selectedRoom', 
                'sensors', 
                'dataList', 
                'selectedSensorId',
                'startDate',
                'devices',
                'endDate',
                'storageUnits',
                'roomusagehistory'
            ));
            
        } catch (Exception $e) {
            Log::error('Lỗi khi hiển thị dữ liệu: ' . $e->getMessage());
            return redirect()->back()->with([
                'error'=> 'Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau',
                'title' => 'Lỗi']);
        }
    }
}