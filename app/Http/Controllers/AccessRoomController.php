<?php

namespace App\Http\Controllers;

use App\Models\DataList;
use App\Models\Device;
use App\Models\DeviceRestrictionSchedule;
use App\Models\DeviceUsageHistory;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\Roomusagehistory;
use App\Models\SystemLog;
use App\Models\Sensor;
use App\Models\Storageunit;
use App\Models\RoomRestrictionSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessRoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        $id_phong = env('ID_PHONG');
        $devices = Device::where('id_phong', $id_phong)->get();
        $sensors = Sensor::where('id_phong', $id_phong)->get();

        $khoaphong = RoomRestrictionSchedule::where(function ($query) {
            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
            $query->where('thoi_gian_bat_dau', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                });
        })->get();

        $khoathietbi = DeviceRestrictionSchedule::where(function ($query) {
            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
            $query->where('thoi_gian_bat_dau', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                });
        })->get();

        $datalist = [];
        $datatime = [];

        // Lọc cảm biến có id_loaicambien là 1 và 4
        $filteredSensors = $sensors->whereIn('id_loaicambien', [1, 4, 6]);

        foreach ($filteredSensors as $sensor) {
            //Thông tin cơ bản
            $storageUnit = null;
            $dataListEntry = DataList::where('id_cambien', $sensor->id)->orderBy('id', 'desc')->first();
            if ($dataListEntry && $dataListEntry->id_donviluutru) {
                $storageUnit = Storageunit::find($dataListEntry->id_donviluutru);
            }
            $storageunits = $storageUnit ? $storageUnit->ten_don_vi_luu_tru : 'N/A';
            $sensorStatus = $sensor->id_trangthai == 1 ? 'Hoạt động' : 'Không hoạt động';
            $title = $sensor->ten_cam_bien . ' - ' . $sensor->ma_so . ' - ' . $sensorStatus;

            //Xử lý dữ liệu
            $sensorData = DataList::where('id_cambien', $sensor->id)
                ->whereBetween('thoi_gian_thu_thap', [
                    now()->setTimezone('Asia/Ho_Chi_Minh')->startOfDay(),
                    now()->setTimezone('Asia/Ho_Chi_Minh')->endOfDay()
                ])
                ->orderBy('thoi_gian_thu_thap', 'asc')
                ->pluck('du_lieu_thu_thap', 'thoi_gian_thu_thap')
                ->toArray();

            $max = !empty($sensorData) ? max(array_values($sensorData)) : 0;
            $min = !empty($sensorData) ? min(array_values($sensorData)) : 0;
            $avg = !empty($sensorData) ? array_sum($sensorData) / count($sensorData) : 0;

            $latestData = DataList::where('id_cambien', $sensor->id)
                ->orderBy('thoi_gian_thu_thap', 'desc')
                ->first();

            $currentValue = $latestData ? $latestData->du_lieu_thu_thap : 0;
            $time = $latestData ? $latestData->thoi_gian_thu_thap : 'NA';

            // Chuyển đổi dữ liệu để dễ dàng sử dụng trong biểu đồ
            $formattedData = [];
            foreach ($sensorData as $time => $value) {
                $formattedData[] = [
                    'time' => date('H:i', strtotime($time)),
                    'value' => $value
                ];
            }

            $datalist[] = [
                'title' => $title,
                'storageunits' => $storageunits,
                'data' => $formattedData,
                'raw_data' => $sensorData
            ];

            $datatime[] = [
                'title' => $sensor->ten_cam_bien . ' - ' . $sensor->ma_so,
                'max' => $max,
                'min' => $min,
                'avg' => $avg,
                'current_value' => $currentValue,
                'time' => $time,
                'storageunits' => $storageunits,
                'sensor_status' => $sensorStatus
            ];
        }




        $filteredSensors = $sensors->whereIn('id_loaicambien', [2]);
        foreach ($filteredSensors as $sensor) {
            //Thông tin cơ bản
            $data = DataList::where('id_cambien', $sensor->id)->first();
            $storageunit = $data ? Storageunit::find($data->id_donviluutru) : null;
            $storageunits = $storageunit ? $storageunit->ten_don_vi_luu_tru : 'N/A';
            $sensorStatus = $sensor->id_trangthai == 1 ? 'Hoạt động' : 'Không hoạt động';
            $title = $sensor->ten_cam_bien . ' - ' . $sensor->ma_so . ' - ' . $sensorStatus;
            //Xử lý dữ liệu

            $sensorDatand = DataList::where('id_cambien', $sensor->id)
                ->where('id_donviluutru', 3)
                ->whereBetween('thoi_gian_thu_thap', [
                    now()->setTimezone('Asia/Ho_Chi_Minh')->startOfDay(),
                    now()->setTimezone('Asia/Ho_Chi_Minh')->endOfDay()
                ])
                ->orderBy('thoi_gian_thu_thap', 'asc')
                ->pluck('du_lieu_thu_thap', 'thoi_gian_thu_thap')
                ->toArray();

            $formattedData = [];
            foreach ($sensorDatand as $time => $value) {
                $formattedData[] = [
                    'time' => date('H:i', strtotime($time)),
                    'value' => $value
                ];
            }

            $datalist[] = [
                'title' => $title,
                'storageunits' => Storageunit::find(3)->ten_don_vi_luu_tru,
                'data' => $formattedData,
                'raw_data' => $sensorDatand
            ];


            $max = !empty($sensorDatand) ? max(array_values($sensorDatand)) : 0;
            $min = !empty($sensorDatand) ? min(array_values($sensorDatand)) : 0;
            $avg = !empty($sensorDatand) ? array_sum($sensorDatand) / count($sensorDatand) : 0;
            $latestData = DataList::where('id_cambien', $sensor->id)
                ->where('id_donviluutru', 3)
                ->orderBy('thoi_gian_thu_thap', 'desc')
                ->first();

            $currentValue = $latestData ? $latestData->du_lieu_thu_thap : 0;
            $time = $latestData ? $latestData->thoi_gian_thu_thap : 'NA';

            $datatime[] = [
                'title' => $sensor->ten_cam_bien . ' - ' . $sensor->ma_so,
                'max' => $max,
                'min' => $min,
                'avg' => $avg,
                'current_value' => $currentValue,
                'time' => $time,
                'storageunits' => $storageunits,
                'sensor_status' => $sensorStatus
            ];


            $sensorDatada = DataList::where('id_cambien', $sensor->id)
                ->where('id_donviluutru', 4)
                ->whereBetween('thoi_gian_thu_thap', [
                    now()->setTimezone('Asia/Ho_Chi_Minh')->startOfDay(),
                    now()->setTimezone('Asia/Ho_Chi_Minh')->endOfDay()
                ])
                ->orderBy('thoi_gian_thu_thap', 'asc')
                ->pluck('du_lieu_thu_thap', 'thoi_gian_thu_thap')
                ->toArray();

            $formattedData = [];
            foreach ($sensorDatada as $time => $value) {
                $formattedData[] = [
                    'time' => date('H:i', strtotime($time)),
                    'value' => $value
                ];
            }

            $datalist[] = [
                'title' => $title,
                'storageunits' => Storageunit::find(4)->ten_don_vi_luu_tru,
                'data' => $formattedData,
                'raw_data' => $sensorDatada
            ];


            $max = !empty($sensorDatada) ? max(array_values($sensorDatada)) : 0;
            $min = !empty($sensorDatada) ? min(array_values($sensorDatada)) : 0;
            $avg = !empty($sensorDatada) ? array_sum($sensorDatada) / count($sensorDatada) : 0;
            $latestData = DataList::where('id_cambien', $sensor->id)
                ->where('id_donviluutru', 4)
                ->orderBy('thoi_gian_thu_thap', 'desc')
                ->first();

            $currentValue = $latestData ? $latestData->du_lieu_thu_thap : 0;
            $time = $latestData ? $latestData->thoi_gian_thu_thap : 'NA';

            $datatime[] = [
                'title' => $sensor->ten_cam_bien . ' - ' . $sensor->ma_so,
                'max' => $max,
                'min' => $min,
                'avg' => $avg,
                'current_value' => $currentValue,
                'time' => $time,
                'storageunits' => $storageunits,
                'sensor_status' => $sensorStatus
            ];
        }

        return view('access-room.index', compact('rooms', 'devices', 'datalist', 'sensors', 'id_phong', 'datatime', 'khoaphong', 'khoathietbi'));
    }

    public function access(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'qrCodeValue' => 'required|string',
                'roomId' => 'required|exists:rooms,id',
            ], [
                'qrCodeValue.required' => 'Vui lòng quét mã QR',
                'qrCodeValue.string' => 'Mã QR không hợp lệ',
                'roomId.required' => 'Vui lòng chọn phòng',
                'roomId.exists' => 'Phòng không tồn tại',
            ]);

            $user = User::find(Auth::id());

            if ($request->qrCodeValue == $user->sdt) {

                Roomusagehistory::create([
                    'id_nguoidung' => $user->id,
                    'id_phong' => $request->roomId,
                    'thoi_gian_bat_dau' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                    'thoi_gian_ket_thuc' => null,
                    'id_trangthaisudung' => 2,
                ]);

                SystemLog::create([
                    'noi_dung_thuc_hien' => "Truy cập phòng " . Room::find($request->roomId)->ten_phong,
                    'id_nguoidung' => Auth::id(),
                    'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                ]);

                $envContent = file_get_contents(base_path('.env'));
                $pattern = '/ID_PHONG=.*/';
                $replacement = 'ID_PHONG=' . $request->roomId;

                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n" . $replacement;
                }

                file_put_contents(base_path('.env'), $envContent);

                $room = Room::find($request->roomId);
                $this->connectMQTT(
                    $room->ten_phong,
                    $user->ho_ten,
                    'access',
                    $room->ten_phong
                );

                return redirect()->route('access-room.index')->with([
                    'success' => 'Đăng nhập thành công',
                    'title' => 'Thông báo'
                ]);
            } else {
                return redirect()->route('access-room.index')->with([
                    'error' => 'Mã QR không hợp lệ. Vui lòng thử lại.',
                    'title' => 'Lỗi truy cập'
                ]);
            }


        } catch (\Exception $e) {
            return redirect()->route('access-room.index')->with([
                'error' => 'Đã xảy ra lỗi. Vui lòng thử lại.',
                'title' => 'Lỗi truy cập'
            ]);
        }

    }

    function close(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'roomId' => 'required|exists:rooms,id'
            ], [
                'roomId.required' => 'Vui lòng chọn phòng',
                'roomId.exists' => 'Phòng không tồn tại',
            ]);

            $roomUsageHistory = Roomusagehistory::where('id_phong', $request->roomId)
                ->where('id_nguoidung', Auth::id())
                ->where('id_trangthaisudung', 2)
                ->orderBy('id', 'desc')
                ->first();

            if ($roomUsageHistory) {
                $roomUsageHistory->update([
                    'id_trangthaisudung' => 1,
                    'thoi_gian_ket_thuc' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                ]);
            }

            SystemLog::create([
                'noi_dung_thuc_hien' => "Đóng cửa phòng " . Room::find($request->roomId)->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            $envContent = file_get_contents(base_path('.env'));
            $pattern = '/ID_PHONG=.*/';
            $replacement = 'ID_PHONG=' . 0;

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n" . $replacement;
            }
            ;

            file_put_contents(base_path('.env'), $envContent);

            $room = Room::find($request->roomId);
            $this->connectMQTT(
                $room->ten_phong,
                User::find(Auth::id())->ho_ten,
                'close',
                $room->ten_phong
            );

            return redirect()->route('access-room.index')->with([
                'success' => 'Đóng cửa thành công',
                'title' => 'Thông báo'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    function connectMQTT($room_device, $ho_ten, $action, $room, $value = 0, )
    {
        $client = new \PhpMqtt\Client\MqttClient("broker.emqx.io", 1883, "computer-lab-" . uniqid());
        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setConnectTimeout(5)
            ->setSocketTimeout(5)
            ->setResendTimeout(10)
            ->setKeepAliveInterval(60)
            ->setDelayBetweenReconnectAttempts(3);
        // Không thiết lập username và password nếu không cần

        try {
            $client->connect($connectionSettings, true);
            $message = json_encode([
                'room_device' => $room_device,
                'ho_ten' => $ho_ten,
                'action' => $action,
                'value' => $value,
                'timestamp' => now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
            ]);
            $client->publish('COMPUTERLABROOM/SENSOR/' . $room, $message, 0, false);
            $client->disconnect();
            \Log::info('MQTT Message sent successfully to COMPUTERLABROOM/SENSOR/' . $room);
        } catch (\Exception $e) {
            // Log MQTT connection error but continue execution
            \Log::error('MQTT Connection Error: ' . $e->getMessage());
            return redirect()->route('access-room.index')->with([
                'error' => 'Đã xảy ra lỗi kết nối MQTT. Vui lòng thử lại.',
                'title' => 'Lỗi truy cập'
            ]);
        }
    }

    function control(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'deviceId' => 'required|exists:devices,id',
                'devicecontrol' => 'required|string',
                'deviceThreshold' => 'nullable|numeric|min:0'
            ], [
                'deviceId.required' => 'Vui lòng chọn thiết bị',
                'deviceId.exists' => 'Thiết bị không tồn tại',
                'devicecontrol.string' => 'Giá trị điều khiển phải là chuỗi',
                'deviceThreshold.required' => 'Vui lòng nhập ngưỡng giá trị',
                'deviceThreshold.numeric' => 'Ngưỡng giá trị phải là số',
                'deviceThreshold.min' => 'Ngưỡng giá trị phải lớn hơn hoặc bằng 0'
            ]);
            return DB::transaction(function () use ($request) {
                $device = Device::where('id', $request->deviceId)->lockForUpdate()->first();
                if ($device) {
                    $device->update([
                        'nguong_dieu_khien' => $request->deviceThreshold
                    ]);

                    $user = User::find(Auth::id());
                    $this->connectMQTT(
                        $device->ma_so,
                        $user->ho_ten,
                        $request->devicecontrol,
                        Room::find($device->id_phong)->ten_phong,
                        $request->deviceThreshold
                    );



                    if ($request->devicecontrol == 'on') {
                        $isRunning = DeviceUsageHistory::where('id_thietbi', $request->deviceId)
                            ->whereNull('thoi_gian_ket_thuc')
                            ->exists();
                        if ($isRunning) {
                            DeviceUsageHistory::create([
                                'id_thietbi' => $request->deviceId,
                                'id_nguoidung' => Auth::id(),
                                'thoi_gian_bat_dau' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                                'thoi_gian_ket_thuc' => null,
                                'id_trangthaisudung' => 2,
                            ]);

                            SystemLog::create([
                                'noi_dung_thuc_hien' => "Bật thiết bị " . Device::find($request->deviceId)->ten_thiet_bi,
                                'id_nguoidung' => Auth::id(),
                                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                            ]);
                        }
                    } else {
                        $deviceUsageHistory = DeviceUsageHistory::where([
                            'id_thietbi' => $request->deviceId,
                            'id_nguoidung' => Auth::id(),
                            'id_trangthaisudung' => 1
                        ])
                            ->whereNull('thoi_gian_ket_thuc')
                            ->lockForUpdate()
                            ->latest('id')
                            ->first();

                        if ($deviceUsageHistory) {
                            $deviceUsageHistory->update([
                                'id_trangthai' => 2,
                                'thoi_gian_ket_thuc' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                            ]);
                            SystemLog::create([
                                'noi_dung_thuc_hien' => "Bật thiết bị " . Device::find($request->deviceId)->ten_thiet_bi,
                                'id_nguoidung' => Auth::id(),
                                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                            ]);
                        } else {
                            DeviceUsageHistory::create([
                                'id_thietbi' => $request->deviceId,
                                'id_nguoidung' => Auth::id(),
                                'thoi_gian_bat_dau' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                                'thoi_gian_ket_thuc' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                                'id_trangthaisudung' => 1,
                            ]);
                        }
                        SystemLog::create([
                            'noi_dung_thuc_hien' => "Tắt thiết bị " . Device::find($request->deviceId)->ten_thiet_bi,
                            'id_nguoidung' => Auth::id(),
                            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                        ]);
                    }
                }
                return redirect()->route('access-room.index')->with([
                    'success' => 'Đã điều khiển',
                    'title' => 'Thông báo'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

