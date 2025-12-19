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
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Traits\SecureSignature;
use App\Services\SensorNormalizer;

class MqttDataController extends Controller
{
    use SecureSignature;

    /**
     * Xử lý dữ liệu MQTT từ agent.
     */
    public function handleMqttData(Request $request, SensorNormalizer $normalizer)
    {
        try {
            Log::info('Nhan du lieu MQTT: ' . $request->getContent());

            $rawContent = $request->getContent();
            $data = $this->verifyPayload($rawContent);
            $legacyPayload = false;

            if ($data === null) {
                // Legacy payload: chưa có chữ ký HMAC, chỉ chấp nhận tạm.
                $legacyPayload = true;
                $data = json_decode($rawContent, true);
            } else {
                $replayKey = 'ingest:' . ($data['nonce'] ?? $data['ts']) . ':' . ($data['Seri'] ?? 'unknown');
                if (!Cache::add($replayKey, 1, 120)) {
                    Log::warning('Rejected replayed MQTT payload', ['key' => $replayKey]);
                    return response()->json(['error' => 'Replay detected'], 409);
                }
            }

            if (!$data) {
                Log::error('Du lieu MQTT khong hop le: ' . $request->getContent());
                return response()->json([
                    'success' => false,
                    'message' => 'Du lieu JSON khong hop le'
                ], 400);
            }

            if ($legacyPayload) {
                Log::warning('Payload MQTT chua duoc ky HMAC, chap nhan o che do legacy.');
            }

            $dataList = null;

            $room = Room::where('ten_phong', $data['Phong'])->first();
            if (!$room) {
                $room = Room::create([
                    'ten_phong' => $data['Phong'],
                    'khu_vuc' => 'Trong',
                    'vi_tri' => 'Trong',
                    'mo_ta' => 'Phong ' . $data['Phong']
                ]);
            }

            $statusString = strtolower((string) ($data['Trangthai'] ?? ''));
            $isOnline = str_contains($statusString, 'hoat') || str_contains($statusString, 'online') || $statusString === '1';

            // Device update
            if (substr($data['Seri'], 0, 2) === 'TB') {
                $device = Device::where('ma_so', $data['Seri'])->first();

                if ($device) {
                    $device->update([
                        'id_trangthai' => $isOnline ? 1 : 2
                    ]);
                } else {
                    Log::warning('Khong tim thay thiet bi cho seri', ['seri' => $data['Seri']]);
                }

                if ($device && $isOnline && $device->id_loaithietbi == 7) {
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
                                        ->subject('Canh bao chay - He thong Quan ly Phong may');
                                });
                            }
                        } catch (\Exception $mailException) {
                            Log::error('Error sending email: ' . $mailException->getMessage());
                        }
                    }
                }

                Log::info('Cap nhat trang thai thiet bi', [
                    'seri' => $data['Seri'],
                    'status' => $data['Trangthai']
                ]);
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
                            'mo_ta' => 'Trong'
                        ]);
                    }

                    $sensor = Sensor::create([
                        'ma_so' => $data['Seri'],
                        'ten_cam_bien' => 'Cam bien phong ' . $data['Phong'],
                        'id_phong' => $room->id,
                        'id_loaicambien' => $typesensor->id,
                        'id_trangthai' => 1,
                        'mo_ta' => 'Trong'
                    ]);
                }

                $storageUnit = StorageUnit::where('ten_don_vi_luu_tru', $data['Donvi'])->first();
                if (!$storageUnit) {
                    $storageUnit = StorageUnit::create([
                        'ten_don_vi_luu_tru' => $data['Donvi'],
                        'mo_ta' => 'Don vi do ' . $data['Donvi']
                    ]);
                }

                if (!$isOnline) {
                    $sensor->update([
                        'id_trangthai' => 2
                    ]);
                } else {
                    $sensor->update([
                        'id_trangthai' => 1
                    ]);
                }

                $normalizedValue = $normalizer->process(
                    $this->mapMeasurementType($data['Donvi'] ?? ''),
                    $data['Value'] ?? null
                );

                if ($normalizedValue === null) {
                    Log::warning('Bo qua du lieu ngoai le', [
                        'seri' => $data['Seri'],
                        'value' => $data['Value'] ?? null,
                        'unit' => $data['Donvi'] ?? null,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Gia tri ngoai nguong'
                    ], 422);
                }

                $dataList = DataList::create([
                    'id_cambien' => $sensor->id,
                    'du_lieu_thu_thap' => $normalizedValue,
                    'id_donviluutru' => $storageUnit->id,
                    'thoi_gian_thu_thap' => date('Y-m-d H:i:s', strtotime($data['Thoigian']))
                ]);

                Log::info('Da luu du lieu cam bien tu MQTT: ' . $data['Seri']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Da luu du lieu cam bien thanh cong',
                'data' => $dataList
            ], 201);
        } catch (\Exception $e) {
            Log::error('Loi khi luu du lieu MQTT: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Co loi xay ra khi luu du lieu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function mapMeasurementType(string $unit): string
    {
        $u = strtolower($unit);
        if (str_contains($u, 'c') || str_contains($u, 'cel')) {
            return 'temperature';
        }
        if (str_contains($u, '%') || str_contains($u, 'hum')) {
            return 'humidity';
        }
        return $u ?: 'raw';
    }
}
