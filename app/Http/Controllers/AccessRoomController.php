<?php

namespace App\Http\Controllers;

use App\Traits\SecureSignature; // Import Trait
use App\Models\ControlEvent;
use App\Models\RoomManager;
use Illuminate\Support\Facades\Cache;
use App\Models\DataList;
use App\Models\Device;
use App\Models\DeviceRestrictionSchedule;
use App\Models\DeviceUsageHistory;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\Roomusagehistory;
use App\Models\RoomSession;
use App\Models\RoomCommand;
use App\Models\SystemLog;
use App\Models\Sensor;
use App\Models\Storageunit;
use App\Models\RoomRestrictionSchedule;
use Illuminate\Support\Facades\Auth;

class AccessRoomController extends Controller
{
    use SecureSignature;
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

        // L???c c???m bi???n c?? id_loaicambien l?? 1 v?? 4
        $filteredSensors = $sensors->whereIn('id_loaicambien', [1, 4, 6]);

        foreach ($filteredSensors as $sensor) {
            //Th??ng tin c?? b???n
            $storageUnit = null;
            $dataListEntry = DataList::where('id_cambien', $sensor->id)->orderBy('id', 'desc')->first();
            if ($dataListEntry && $dataListEntry->id_donviluutru) {
                $storageUnit = Storageunit::find($dataListEntry->id_donviluutru);
            }
            $storageunits = $storageUnit ? $storageUnit->ten_don_vi_luu_tru : 'N/A';
            $sensorStatus = $sensor->id_trangthai == 1 ? 'Ho???t ?????ng' : 'Kh??ng ho???t ?????ng';
            $title = $sensor->ten_cam_bien . ' - ' . $sensor->ma_so . ' - ' . $sensorStatus;

            //X??? l?? d??? li???u
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

            // Chuy???n ?????i d??? li???u ????? d??? d??ng s??? d???ng trong bi???u ?????
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
            //Th??ng tin c?? b???n
            $data = DataList::where('id_cambien', $sensor->id)->first();
            $storageunit = $data ? Storageunit::find($data->id_donviluutru) : null;
            $storageunits = $storageunit ? $storageunit->ten_don_vi_luu_tru : 'N/A';
            $sensorStatus = $sensor->id_trangthai == 1 ? 'Ho???t ?????ng' : 'Kh??ng ho???t ?????ng';
            $title = $sensor->ten_cam_bien . ' - ' . $sensor->ma_so . ' - ' . $sensorStatus;
            //X??? l?? d??? li???u

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
        $request->validate([
            'qrCodeValue' => 'required|string',
            'roomId' => 'required|exists:rooms,id',
            'override' => 'sometimes|boolean',
            'override_reason' => 'sometimes|string|nullable'
        ]);

        $roomId = $request->roomId;
        $lock = Cache::lock('access_room_' . $roomId, 5);
        if (!$lock->get()) {
            return redirect()->route('access-room.index')->with([
                'error' => 'Phong dang ban, vui long thu lai.',
                'title' => 'Loi truy cap'
            ]);
        }

        try {
            $user = User::find(Auth::id());
            $room = Room::find($roomId);
            $override = $request->boolean('override');

            $activeSession = RoomSession::where('room_id', $roomId)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();

            if ($activeSession && !$override) {
                return redirect()->route('access-room.index')->with([
                    'error' => 'Phong dang co phien hoat dong. Yeu cau override hoac cho doi.',
                    'title' => 'Phong ban'
                ]);
            }

            if ($override && $activeSession) {
                $activeSession->update([
                    'status' => 'override_closed',
                    'ended_at' => now(),
                    'override' => true,
                    'override_reason' => $request->override_reason
                ]);
            }

            if ($request->qrCodeValue != $user->sdt) {
                return redirect()->route('access-room.index')->with([
                    'error' => 'Ma QR khong hop le. Vui long thu lai.',
                    'title' => 'Loi truy cap'
                ]);
            }

            $session = RoomSession::create([
                'room_id' => $roomId,
                'user_id' => $user->id,
                'started_at' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                'expires_at' => now()->setTimezone('Asia/Ho_Chi_Minh')->addHours(2),
                'status' => 'active',
                'override' => $override,
                'override_reason' => $request->override_reason
            ]);

            Roomusagehistory::create([
                'id_nguoidung' => $user->id,
                'id_phong' => $roomId,
                'thoi_gian_bat_dau' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                'thoi_gian_ket_thuc' => null,
                'id_trangthaisudung' => 2,
            ]);

            SystemLog::create([
                'noi_dung_thuc_hien' => "Truy cap phong " . $room->ten_phong,
                'id_nguoidung' => $user->id,
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            $command = RoomCommand::create([
                'room_id' => $roomId,
                'user_id' => $user->id,
                'session_id' => $session->id,
                'command' => 'access',
                'payload' => ['room' => $room->ten_phong]
            ]);

            $this->connectMQTT(
                $room->ten_phong,
                $user->ho_ten,
                'access',
                $room->ten_phong,
                0,
                $session->id,
                $command->id
            );

            return redirect()->route('access-room.index')->with([
                'success' => 'Dang nhap thanh cong',
                'title' => 'Thong bao'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('access-room.index')->with([
                'error' => 'Co loi xay ra. Vui long thu lai.',
                'title' => 'Loi truy cap'
            ]);
        } finally {
            $lock->release();
        }
    }
    public function close(Request $request)
    {
        $request->validate([
            'roomId' => 'required|exists:rooms,id'
        ]);

        $roomId = $request->roomId;
        $room = Room::find($roomId);
        $session = RoomSession::where('room_id', $roomId)
            ->where('status', 'active')
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->first();

        if ($session) {
            $session->update([
                'status' => 'closed',
                'ended_at' => now()->setTimezone('Asia/Ho_Chi_Minh')
            ]);
        }

        if ($session) {
            Roomusagehistory::where('id_phong', $roomId)
                ->where('id_nguoidung', Auth::id())
                ->whereNull('thoi_gian_ket_thuc')
                ->latest('id')
                ->limit(1)
                ->update([
                    'id_trangthaisudung' => 1,
                    'thoi_gian_ket_thuc' => now()->setTimezone('Asia/Ho_Chi_Minh')
                ]);
        }

        SystemLog::create([
            'noi_dung_thuc_hien' => "Dong cua phong " . $room->ten_phong,
            'id_nguoidung' => Auth::id(),
            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        $command = RoomCommand::create([
            'room_id' => $roomId,
            'user_id' => Auth::id(),
            'session_id' => optional($session)->id,
            'command' => 'close',
            'payload' => ['room' => $room->ten_phong]
        ]);

        $this->connectMQTT(
            $room->ten_phong,
            User::find(Auth::id())->ho_ten,
            'close',
            $room->ten_phong,
            0,
            optional($session)->id,
            $command->id
        );

        return redirect()->route('access-room.index')->with([
            'success' => 'Dong cua thanh cong',
            'title' => 'Thong bao'
        ]);
    }
    public function control(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|exists:devices,id',
            'devicecontrol' => 'required|string',
            'deviceThreshold' => 'nullable|numeric|min:0',
            'override' => 'sometimes|boolean',
            'override_reason' => 'sometimes|string|nullable'
        ]);

        $device = Device::find($request->deviceId);
        $room = Room::find($device->id_phong);
        $session = RoomSession::where('room_id', $room->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('id')
            ->first();

        $override = $request->boolean('override');
        if (!$session && !$override) {
            return redirect()->route('access-room.index')->with([
                'error' => 'Khong co phien hoat dong cho phong nay. Mo phong truoc khi dieu khien.',
                'title' => 'Loi dieu khien'
            ]);
        }

        if ($override && $session && $session->user_id !== Auth::id()) {
            $session->update([
                'status' => 'override_closed',
                'ended_at' => now(),
                'override' => true,
                'override_reason' => $request->override_reason
            ]);
            $session = null;
        }

        $device->update([
            'nguong_dieu_khien' => $request->deviceThreshold
        ]);

        $command = RoomCommand::create([
            'room_id' => $room->id,
            'device_id' => $device->id,
            'user_id' => Auth::id(),
            'session_id' => optional($session)->id,
            'command' => $request->devicecontrol,
            'payload' => [
                'threshold' => $request->deviceThreshold
            ],
            'status' => 'pending'
        ]);

        $user = User::find(Auth::id());
        $this->connectMQTT(
            $device->ma_so,
            $user->ho_ten,
            $request->devicecontrol,
            $room->ten_phong,
            $request->deviceThreshold,
            optional($session)->id,
            $command->id
        );

        ControlEvent::create([
            'room_id' => $room->id,
            'device_id' => $device->id,
            'user_id' => Auth::id(),
            'session_id' => optional($session)->id,
            'command' => $request->devicecontrol,
            'payload' => ['threshold' => $request->deviceThreshold],
            'status' => 'sent',
            'executed_at' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        return redirect()->route('access-room.index')->with([
            'success' => 'Da gui lenh dieu khien',
            'title' => 'Thong bao'
        ]);
    }

    function connectMQTT($room_device, $ho_ten, $action, $room, $value = 0, $sessionId = null, $commandId = null)
    {
        $client = new \PhpMqtt\Client\MqttClient("broker.emqx.io", 1883, "computer-lab-" . uniqid());
        $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setConnectTimeout(5)
            ->setSocketTimeout(5)
            ->setResendTimeout(10)
            ->setKeepAliveInterval(60)
            ->setDelayBetweenReconnectAttempts(3);

        try {
            $client->connect($connectionSettings, true);
            $payload = [
                'room_device' => $room_device,
                'ho_ten' => $ho_ten,
                'action' => $action,
                'value' => $value,
                'room' => $room,
                'session_id' => $sessionId,
                'command_id' => $commandId,
                'nonce' => uniqid('cmd-', true),
            ];

            $signed = $this->signPayload($payload);

            $client->publish('COMPUTERLABROOM/SENSOR/' . $room, json_encode($signed), 0, false);
            $client->disconnect();
            \Log::info('MQTT Message sent successfully to COMPUTERLABROOM/SENSOR/' . $room);
        } catch (\Exception $e) {
            \Log::error('MQTT Connection Error: ' . $e->getMessage());
        }
    }
}
