<?php

namespace App\Http\Controllers;

use App\Models\DataList;
use App\Models\Sensor;
use App\Models\Room;
use App\Models\SensorType;
use App\Models\StorageUnit;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\RoomManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;


class MqttDataController extends Controller
{
    /**
     * Xử lý dữ liệu từ MQTT
     */
    public function handleMqttData(Request $request)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $secretKey = env('SENSOR_SECRET_KEY', 'bi_mat_khong_the_bat_mi');

            if (!isset($data['signature']) || !isset($data['timestamp'])) {
                Log::warning('Gói tin thiếu chữ ký hoặc timestamp: ' . $data['Seri']);
                return response()->json(['message' => 'Missing Signature'], 403);
            }

            $packetTime = strtotime($data['timestamp']); // Sensor phải gửi kèm timestamp
            if (abs(time() - $packetTime) > 300) {
                Log::warning('Phát hiện Replay Attack: ' . $data['Seri']);
                return response()->json(['message' => 'Packet Expired'], 403);
            }

            $payloadToSign = $data['Seri'] . '|' . $data['Value'] . '|' . $data['Thoigian'];

            $calculatedSignature = hash_hmac('sha256', $payloadToSign, $secretKey);

            if (!hash_equals($calculatedSignature, $data['signature'])) {
                Log::alert('CẢNH BÁO: Dữ liệu bị thay đổi trên đường truyền! ' . $data['Seri']);
                return response()->json(['message' => 'Invalid Signature'], 403);
            }

            // Ghi log để kiểm tra dữ liệu đầu vào
            Log::info('Nhận dữ liệu MQTT: ' . $request->getContent());

            // Lấy dữ liệu từ request
            $data = json_decode($request->getContent(), true);


            if (!$data) {
                Log::error('Dữ liệu MQTT không hợp lệ: ' . $request->getContent());
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu JSON không hợp lệ'
                ], 400);
            }



            $room = Room::where('ten_phong', $data['Phong'])->first();
            if (!$room) {
                $room = Room::create([
                    'ten_phong' => $data['Phong'],
                    'khu_vuc' => 'Trống',
                    'vi_tri' => 'Trống',
                    'mo_ta' => 'Phòng ' . $data['Phong']
                ]);
            }


            // Check if sensor code starts with "Tb"
            if (substr($data['Seri'], 0, 2) === 'TB') {
                $device = Device::where('ma_so', $data['Seri'])->first();
                if ($data['Trangthai'] != 'Hoạt động') {
                    $device->update([
                        'id_trangthai' => 2
                    ]);
                } else {
                    $device->update([
                        'id_trangthai' => 1
                    ]);
                }

                if ($data['Trangthai'] == 'Hoạt động' && $device->id_loaithietbi == 7) {
                    $roomManagers = RoomManager::where('id_phong', $room->id)->get();

                    foreach ($roomManagers as $manager) {
                        try {
                            $user = User::find($manager->id_nguoidung);

                            if ($user && $user->email) {
                                Mail::send('emails.baochay', [
                                    'location' => $room->ten_phong . ' - ' . $room->khu_vuc . ' - ' . $room->vi_tri,
                                    'time' => date('Y-m-d H:i:s', strtotime($data['Thoigian'])),
                                    'emergency_contact' => '0349930924'
                                ], function ($message) use ($user) {
                                    $message->to($user->email)
                                        ->subject('Cảnh báo cháy - Hệ thống Quản lý Phòng máy');
                                });
                            }
                        } catch (\Exception $mailException) {
                            Log::error('Error sending email: ' . $mailException->getMessage());
                        }
                    }
                }

                Log::error('Device error: ' . $data['Seri'] . ' - Status: ' . $data['Trangthai']);
            } else {
                $sensor = Sensor::where('ma_so', $data['Seri'])->first();
                if (!$sensor) {
                    $typesensor = SensorType::where(function ($query) use ($data) {
                        $query->where('ten_loai_cam_bien', 'LIKE', '%' . $data['Donvi'] . '%')
                            ->orWhere('mo_ta', 'LIKE', '%' . $data['Donvi'] . '%');
                    })->first();


                    if (!$typesensor) {
                        $typesensor = SensorType::create([
                            'ten_loai_cam_bien' => $data['Donvi'],
                            'mo_ta' => 'Trống'
                        ]);
                    }


                    $sensor = Sensor::create([
                        'ma_so' => $data['Seri'],
                        'ten_cam_bien' => 'Cảm biến phòng ' . $data['Phong'],
                        'id_phong' => $room->id,
                        'id_loaicambien' => $typesensor->id,
                        'id_trangthai' => 1,
                        'mo_ta' => 'Trống'
                    ]);
                }

                $storageUnit = StorageUnit::where('ten_don_vi_luu_tru', $data['Donvi'])->first();
                if (!$storageUnit) {
                    $storageUnit = StorageUnit::create([
                        'ten_don_vi_luu_tru' => $data['Donvi'],
                        'mo_ta' => 'Đơn vị đo ' . $data['Donvi']
                    ]);
                }


                if ($data['Trangthai'] != 'Hoạt động') {
                    $sensor->update([
                        'id_trangthai' => 2
                    ]);
                } else {
                    $sensor->update([
                        'id_trangthai' => 1
                    ]);
                }

                // Lưu dữ liệu vào bảng datalist
                $dataList = DataList::create([
                    'id_cambien' => $sensor->id,
                    'du_lieu_thu_thap' => $data['Value'],
                    'id_donviluutru' => $storageUnit->id,
                    'thoi_gian_thu_thap' => date('Y-m-d H:i:s', strtotime($data['Thoigian']))
                ]);

                Log::info('Đã lưu dữ liệu cảm biến từ MQTT: ' . $data['Seri']);
            }



            return response()->json([
                'success' => true,
                'message' => 'Đã lưu dữ liệu cảm biến thành công',
                'data' => $dataList
            ], 201);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lưu dữ liệu MQTT: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu dữ liệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}