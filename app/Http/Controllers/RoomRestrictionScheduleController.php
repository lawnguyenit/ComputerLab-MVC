<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomRestrictionSchedule;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;
class RoomRestrictionScheduleController extends Controller
{
    /**
     * Hiển thị danh sách lịch cấm sử dụng phòng
     */
    public function index()
    {
        try {
            $roomRestrictions = RoomRestrictionSchedule::with('room')->get();
            $rooms = Room::all();
            return view('room_restrictions.index', compact('roomRestrictions', 'rooms'));
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tải danh sách.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Lưu lịch cấm sử dụng phòng mới vào database
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_phong' => 'required|exists:rooms,id',
                'noi_dung_cam_su_dung' => 'required|string|max:255',
                'thoi_gian_bat_dau' => 'required|date',
                'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check for conflicting restrictions
            $conflictingRestrictions = RoomRestrictionSchedule::where('id_phong', $request->id_phong)
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

            $roomRestriction = RoomRestrictionSchedule::create([
                'id_phong' => $request->id_phong,
                'noi_dung_cam_su_dung' => $request->noi_dung_cam_su_dung,
                'thoi_gian_bat_dau' => $request->thoi_gian_bat_dau,
                'thoi_gian_ket_thuc' => $request->thoi_gian_ket_thuc,
            ]);

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Tạo lịch cấm sử dụng phòng mới: ' . $roomRestriction->noi_dung_cam_su_dung,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('room-restrictions.index')->with([
                "success" => "Thêm khóa phòng máy thành công.",
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
     * Hiển thị form chỉnh sửa lịch cấm sử dụng phòng
     */
    public function edit($id)
    {
        try {
            $roomRestriction = RoomRestrictionSchedule::findOrFail($id);
            $rooms = Room::all();
            
            if (request()->ajax()) {
                return response()->json([
                    'roomrestriction' => $roomRestriction,
                ]);
            }
            
            return view('room-restrictions.edit', compact('roomRestriction', 'rooms'));
            
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
     * Cập nhật lịch cấm sử dụng phòng
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_phong' => 'required|exists:rooms,id',
                'noi_dung_cam_su_dung' => 'required|string|max:255',
                'thoi_gian_bat_dau' => 'required|date',
                'thoi_gian_ket_thuc' => 'required|date|after:thoi_gian_bat_dau',
            ],[
                'id_phong.required' => 'Vui lòng chọn phòng.',
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

            $roomRestriction = RoomRestrictionSchedule::findOrFail($id);

            // Check for conflicting restrictions excluding current record
            $conflictingRestrictions = RoomRestrictionSchedule::where('id_phong', $request->id_phong)
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

            $roomRestriction->update([
                'id_phong' => $request->id_phong,
                'noi_dung_cam_su_dung' => $request->noi_dung_cam_su_dung,
                'thoi_gian_bat_dau' => $request->thoi_gian_bat_dau,
                'thoi_gian_ket_thuc' => $request->thoi_gian_ket_thuc,
            ]);

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật lịch cấm sử dụng phòng: ' . $roomRestriction->noi_dung_cam_su_dung,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('room-restrictions.index')->with([
                'success' => 'Đã cập nhật lịch cấm sử dụng phòng thành công.',
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
     * Xóa lịch cấm sử dụng phòng
     */
    public function destroy($id)
    {
        try {
            $roomRestriction = RoomRestrictionSchedule::findOrFail($id);
            $restrictionInfo = $roomRestriction->noi_dung_cam_su_dung;
            
            $roomRestriction->delete();

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa lịch cấm sử dụng phòng: ' . $restrictionInfo,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('room-restrictions.index')->with([
                'success' => 'Đã xóa lịch cấm sử dụng phòng thành công.',
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
     * Xuất mẫu Excel để nhập dữ liệu
     */
    public function exportTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $headers = ['Tên phòng (*)', 'Nội dung cấm sử dụng (*)', 'Thời gian bắt đầu (*)', 'Thời gian kết thúc (*)'];

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

            $sheet->setCellValue('A2', 'Phòng máy A0101');
            $sheet->setCellValue('B2', 'Bảo trì máy tính');
            $sheet->setCellValue('C2', date('Y-m-d H:i:s'));
            $sheet->setCellValue('D2', date('Y-m-d H:i:s', strtotime('+1 day')));

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'mau_import_lich_cam_su_dung_phong_' . date('Y-m-d') . '.xlsx';
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
     * Nhập dữ liệu từ file Excel
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

            // Format headers
            $headers = [
                'Tên phòng (*)',
                'Nội dung cấm sử dụng (*)', 
                'Thời gian bắt đầu (*)',
                'Thời gian kết thúc (*)'
            ];

            foreach ($headers as $index => $header) {
                $sheet->setCellValue(chr(65 + $index) . '1', $header);
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
                        'rgb' => 'E2EFDA',
                    ],
                ],
            ];
            
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            $rows = $sheet->toArray();
            array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($rows as $index => $row) {
                try {
                    if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                        $errors[] = "Dòng " . ($index + 2) . ": Thiếu thông tin bắt buộc";
                        $errorCount++;
                        continue;
                    }
                    
                    $room = Room::where('ten_phong', $row[0])->first();
                    if (!$room) {
                        $errorCount++;
                        continue;
                    }

                    $startTime = strtotime($row[2]);
                    $endTime = strtotime($row[3]);
                    if ($startTime === false || $endTime === false || $startTime >= $endTime) {
                        $errorCount++;
                        continue;
                    }

                    $conflictingRestrictions = RoomRestrictionSchedule::where('id_phong', $room->id)    
                        ->where(function ($query) use ($row) {
                            $query->where('thoi_gian_bat_dau', '<', $row[3])
                                ->where('thoi_gian_ket_thuc', '>', $row[2]);
                        })->exists();
                    if ($conflictingRestrictions) {
                        $errorCount++;
                        continue;
                    }

                    RoomRestrictionSchedule::create([
                        'id_phong' => $room->id,
                        'noi_dung_cam_su_dung' => $row[1],
                        'thoi_gian_bat_dau' => $row[2],
                        'thoi_gian_ket_thuc' => $row[3],
                    ]);
                    $successCount++;
                } catch (Exception $e) {
                    $errorCount++;
                }
            }
            
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Nhập dữ liệu lịch cấm sử dụng phòng từ file Excel: ' . $successCount . ' thành công, ' . $errorCount . ' lỗi',
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('room-restrictions.index')->with([
                'success' => "Thành công: $successCount: Thất bại: $errorCount",
                'title' => 'Import lịch khóa phòng'
            ]);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi nhập file: ' . $e->getMessage(),
                'title' => 'Lỗi'
            ]);
        }
    }
}