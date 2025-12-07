<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceRestrictionSchedule;
use App\Models\Room;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;

class DeviceRestrictionScheduleController extends Controller
{
    /**
     * Hiển thị danh sách lịch cấm sử dụng thiết bị
     */
    public function index()
    {
        try {
            $deviceRestrictions = DeviceRestrictionSchedule::with('device.room')->get();
            $rooms = Room::all();
            $devices=Device::all();
            return view('device_restrictions.index', compact('deviceRestrictions', 'rooms', 'devices'));
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tải danh sách.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Lấy danh sách thiết bị theo phòng
     */
    public function getDevicesByRoom($roomId)
    {
        try {
            $devices = Device::where('id_phong', $roomId)->get();
            return response()->json([
                'success' => true,
                'devices' => $devices
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách thiết bị.'
            ]);
        }
    }

    /**
     * Lưu lịch cấm sử dụng thiết bị mới vào database
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_thietbi' => 'required|exists:devices,id',
                'noi_dung_cam_su_dung' => 'required|string|max:255',
                'thoi_gian_bat_dau' => 'required|date',
                'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Kiểm tra xem có lịch trùng không
            $conflictingRestrictions = DeviceRestrictionSchedule::where('id_thietbi', $request->id_thietbi)
                ->where(function ($query) use ($request) {
                    $query->where('thoi_gian_bat_dau', '<', $request->thoi_gian_ket_thuc)
                        ->where('thoi_gian_ket_thuc', '>', $request->thoi_gian_bat_dau);
                })->exists();

            if ($conflictingRestrictions) {
                return redirect()->back()->with([
                    'error' => 'Thời gian bị trùng với lịch cấm sử dụng khác.',
                    'title' => 'Lỗi'
                ])->withInput();
            }

            $deviceRestriction = DeviceRestrictionSchedule::create([
                'id_thietbi' => $request->id_thietbi,
                'noi_dung_cam_su_dung' => $request->noi_dung_cam_su_dung,
                'thoi_gian_bat_dau' => $request->thoi_gian_bat_dau,
                'thoi_gian_ket_thuc' => $request->thoi_gian_ket_thuc,
            ]);

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Tạo lịch cấm sử dụng thiết bị mới: ' . $deviceRestriction->noi_dung_cam_su_dung,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('device-restrictions.index')->with([
                "success" => "Thêm khóa thiết bị thành công.",
                "title" => "Thêm khóa"
            ]);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi thêm mới.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }

    /**
     * Hiển thị form chỉnh sửa lịch cấm sử dụng thiết bị
     */
    public function edit($id)
    {
        try {
            $deviceRestriction = DeviceRestrictionSchedule::findOrFail($id);
            $devices = Device::all();
            $id_phong = $deviceRestriction->device->id_phong;
            
            if (request()->ajax()) {
                return response()->json([
                    'devicerestriction' => $deviceRestriction,
                    'id_phong' => $id_phong
                ]);
            }
            
            return view('device-restrictions.edit', compact('deviceRestriction', 'devices'));
            
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Không tìm thấy dữ liệu'], 404);
            }
            return redirect()->back()->with([
                'error' => 'Không tìm thấy dữ liệu cần chỉnh sửa.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Cập nhật lịch cấm sử dụng thiết bị
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_thietbi' => 'required|exists:devices,id',
                'noi_dung_cam_su_dung' => 'required|string|max:255',
                'thoi_gian_bat_dau' => 'required|date',
                'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            ],[
                'id_thietbi.required' => 'Vui lòng chọn thiết bị.',
                'noi_dung_cam_su_dung.required' => 'Vui lòng nhập nội dung cấm sử dụng.',
                'thoi_gian_bat_dau.required' => 'Vui lòng nhập thời gian bắt đầu.',
                'thoi_gian_ket_thuc.required' => 'Vui lòng nhập thời gian kết thúc.',
                'thoi_gian_ket_thuc.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $deviceRestriction = DeviceRestrictionSchedule::findOrFail($id);

            // Kiểm tra xem có lịch trùng không
            $conflictingRestrictions = DeviceRestrictionSchedule::where('id_thietbi', $request->id_thietbi)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    $query->where('thoi_gian_bat_dau', '<', $request->thoi_gian_ket_thuc)
                        ->where('thoi_gian_ket_thuc', '>', $request->thoi_gian_bat_dau);
                })->exists();

            if ($conflictingRestrictions) {
                return redirect()->back()->with([
                    'error' => 'Thời gian bị trùng với lịch cấm sử dụng khác.',
                    'title' => 'Lỗi'
                ])->withInput();
            }

            $deviceRestriction->update([
                'id_thietbi' => $request->id_thietbi,
                'noi_dung_cam_su_dung' => $request->noi_dung_cam_su_dung,
                'thoi_gian_bat_dau' => $request->thoi_gian_bat_dau,
                'thoi_gian_ket_thuc' => $request->thoi_gian_ket_thuc,
            ]);

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật lịch cấm sử dụng thiết bị: ' . $deviceRestriction->noi_dung_cam_su_dung,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('device-restrictions.index')->with([
                'success' => 'Đã cập nhật lịch cấm sử dụng thiết bị thành công.',
                'title' => 'Cập nhật'
            ]);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi cập nhật.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }

    /**
     * Xóa lịch cấm sử dụng thiết bị
     */
    public function destroy($id)
    {
        try {
            $deviceRestriction = DeviceRestrictionSchedule::findOrFail($id);
            $restrictionInfo = $deviceRestriction->noi_dung_cam_su_dung;
            
            $deviceRestriction->delete();

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa lịch cấm sử dụng thiết bị: ' . $restrictionInfo,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('device-restrictions.index')->with([
                'success' => 'Đã xóa lịch cấm sử dụng thiết bị thành công.',
                'title' => 'Xóa'
            ]);
            
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi xóa.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Export template file for device restriction schedule import
     */
    public function exportTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $headers = ['Tên thiết bị (*)', 'Nội dung cấm sử dụng (*)', 'Thời gian bắt đầu (*)', 'Thời gian kết thúc (*)'];

            foreach ($headers as $index => $header) {
                $sheet->setCellValue(chr(65 + $index) . '1', $header);
            }

            $columnWidths = [
                'A' => 60,
                'B' => 40, 
                'C' => 30,
                'D' => 30
            ];

            foreach ($columnWidths as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }

            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
            ];
            
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'PC-A0101-01');
            $sheet->setCellValue('B2', 'Bảo trì thiết bị');
            $sheet->setCellValue('C2', date('Y-m-d H:i:s'));
            $sheet->setCellValue('D2', date('Y-m-d H:i:s', strtotime('+1 day')));

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'mau_import_lich_cam_su_dung_thiet_bi_' . date('Y-m-d') . '.xlsx';
            $tempPath = storage_path('app/public/' . $filename);
            $writer->save($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi xuất file mẫu.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Import device restriction schedules from Excel file
     */
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            
            $rows = $sheet->toArray();
            array_shift($rows); // Remove header row
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($rows as $index => $row) {
                try {
                    if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                        $errors[] = "Dòng " . ($index + 2) . ": Thiếu thông tin bắt buộc";
                        $errorCount++;
                        continue;
                    }
                    
                    $device = Device::where('ten_thietbi', $row[0])->first();
                    if (!$device) {
                        $errors[] = "Dòng " . ($index + 2) . ": Không tìm thấy thiết bị";
                        $errorCount++;
                        continue;
                    }

                    $startTime = strtotime($row[2]);
                    $endTime = strtotime($row[3]);
                    if ($startTime === false || $endTime === false || $startTime >= $endTime) {
                        $errors[] = "Dòng " . ($index + 2) . ": Thời gian không hợp lệ";
                        $errorCount++;
                        continue;
                    }

                    $conflictingRestrictions = DeviceRestrictionSchedule::where('id_thietbi', $device->id)
                        ->where(function ($query) use ($row) {
                            $query->where('thoi_gian_bat_dau', '<', $row[3])
                                ->where('thoi_gian_ket_thuc', '>', $row[2]);
                        })->exists();

                    if ($conflictingRestrictions) {
                        $errors[] = "Dòng " . ($index + 2) . ": Thời gian bị trùng lặp";
                        $errorCount++;
                        continue;
                    }

                    DeviceRestrictionSchedule::create([
                        'id_thietbi' => $device->id,
                        'noi_dung_cam_su_dung' => $row[1],
                        'thoi_gian_bat_dau' => $row[2],
                        'thoi_gian_ket_thuc' => $row[3],
                    ]);
                    $successCount++;

                } catch (Exception $e) {
                    $errors[] = "Dòng " . ($index + 2) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            SystemLog::create([
                'noi_dung_thuc_hien' => "Nhập dữ liệu lịch cấm sử dụng thiết bị từ Excel: $successCount thành công, $errorCount thất bại",
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('device-restrictions.index')->with([
                'success' => "Thành công: $successCount, Thất bại: $errorCount",
                'title' => 'Nhập lịch cấm sử dụng thiết bị'
            ]);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi nhập file: ' . $e->getMessage(),
                'title' => 'Lỗi'
            ]);
        }
    }
}