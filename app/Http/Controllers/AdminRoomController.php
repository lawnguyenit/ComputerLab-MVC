<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\DeviceRestrictionSchedule;
use App\Models\RoomManager;
use App\Models\DeviceType;
use App\Models\DataList;
use App\Models\DeviceUsageHistory;
use App\Models\StorageUnit;
use App\Models\RoomUsageHistory;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminRoomController extends Controller
{
    /**
     * Hiển thị danh sách phòng máy mà người dùng được quản lý
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $managedRooms = RoomManager::where('id_nguoidung', $user->id)->first();
        if (!$managedRooms) {
            return redirect()->back()->with([
                'error' => 'Bạn không có quyền truy cập vào trang này',
                'title' => 'Thông báo'
            ]);
        } else {
            $room = Room::find($managedRooms->id_phong);
            $devices = Device::where('id_phong', $room->id)->get();
            $deviceTypes = DeviceType::all();
            $khoathietbi = DeviceRestrictionSchedule::where(function ($query) {
                $now = now()->setTimezone('Asia/Ho_Chi_Minh');
                $query->where('thoi_gian_bat_dau', '<=', $now)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('thoi_gian_ket_thuc')
                            ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                    });
            })->get();

            $sensors = Sensor::where('id_phong', $room->id)->get();
            $list = [];
            foreach ($sensors as $sensor) {
                if ($sensor->id_loaicambien != 2) {
                    $data = DataList::where('id_cambien', $sensor->id)->orderBy('id', 'desc')->first();
                    if ($data) {
                        $list[$sensor->id] = $data->du_lieu_thu_thap . ' ' . StorageUnit::find($data->id_donviluutru)->ten_don_vi_luu_tru;
                    }
                } else {
                    $chuoi = '';
                    $data = DataList::where('id_cambien', $sensor->id)
                        ->where('id_donviluutru', 3)
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($data) {
                        $chuoi = $data->du_lieu_thu_thap . ' ' . StorageUnit::find($data->id_donviluutru)->ten_don_vi_luu_tru;
                    } else {
                        $chuoi = 'NULL';
                    }
                    $data = DataList::where('id_cambien', $sensor->id)
                        ->where('id_donviluutru', 4)
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($data) {
                        $chuoi = $chuoi . ' - ' . $data->du_lieu_thu_thap . ' ' . StorageUnit::find($data->id_donviluutru)->ten_don_vi_luu_tru;
                    } else {
                        $chuoi = $chuoi . ' - NULL';
                    }

                    $list[$sensor->id] = $chuoi;
                }
            }

            // Get sensor data from datalist
            $selectedSensorId = $request->input('sensor_id');

            // Lấy khoảng thời gian nếu có
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Khởi tạo query để lấy dữ liệu
            $query = DataList::with(['sensor', 'storageUnit'])
                ->byRoom($room->id);

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
                ->where(function ($query) use ($now) {
                    $query->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                })
                ->get();
            $accessroom = RoomUsageHistory::where('id_phong', $room->id)
                ->orderBy('id', 'desc')
                ->get();
            // Get all devices in the room
            $thietbi = Device::where('id_phong', $room->id)
                ->pluck('id')
                ->toArray();

            // Get latest device usage history for each device in the room
            $controldevice = DeviceUsageHistory::whereIn('id_thietbi', $thietbi)
                ->orderBy('thoi_gian_bat_dau', 'desc')->get();

            return view('admin-room.index', compact('room', 'devices', 'khoathietbi', 'deviceTypes', 'sensors', 'list', 'roomusagehistory', 'dataList', 'storageUnits', 'selectedSensorId', 'startDate', 'endDate', 'selectedSensorId', 'accessroom', 'controldevice'));

        }
    }
    public function verifyLogIntegrity()
    {
        $logs = SystemLog::orderBy('id', 'asc')->get();
        $alerts = [];

        foreach ($logs as $key => $log) {
            if ($key == 0)
                continue; // Bỏ qua dòng đầu

            $prevLog = $logs[$key - 1];

            // Kiểm tra 1: Hash cũ được lưu có khớp với Hash thật của dòng trước không?
            if ($log->previous_hash !== $prevLog->hash) {
                $alerts[] = "Đứt gãy chuỗi tại ID: " . $log->id . ". Log ID " . $prevLog->id . " có thể đã bị xóa hoặc sửa.";
            }

            // Kiểm tra 2: Tính toán lại Hash hiện tại xem có khớp nội dung không
            $recalculatedHash = hash('sha256', $log->noi_dung_thuc_hien . $log->id_nguoidung . $log->thoi_gian_thuc_hien . $log->previous_hash);
            if ($recalculatedHash !== $log->hash) {
                $alerts[] = "Dữ liệu tại ID " . $log->id . " đã bị sửa đổi trái phép!";
            }
        }

        return $alerts; // Nếu mảng rỗng -> Hệ thống toàn vẹn
    }
}