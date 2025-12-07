<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorType;
use App\Models\Room;
use App\Models\Status;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;

class SensorController extends Controller
{
    /**
     * Hiển thị danh sách cảm biến
     */
    public function index()
    {
        try {
            $sensors = Sensor::with(['sensorType', 'room', 'status'])->get();
            $sensorTypes = SensorType::all();
            $rooms = Room::all();
            $statuses = Status::all();
            return view('sensors.index', compact('sensors', 'sensorTypes', 'rooms', 'statuses'))
                ->with(['success' => 'Tải danh sách cảm biến thành công', 'title' => 'Danh sách cảm biến']);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tải danh sách.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Hiển thị form tạo cảm biến mới
     */
    public function create()
    {
        try {
            $sensorTypes = SensorType::all();
            $rooms = Room::all();
            $statuses = Status::all();
            return view('sensors.create', compact('sensorTypes', 'rooms', 'statuses'));
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tải form.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Lưu cảm biến mới vào database
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ten_cam_bien' => 'required|string|max:255',
                'ma_so' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:sensors',
                    'regex:/^CB.*$/' // Validate that ma_so starts with 'CB'
                ],
                'id_loaicambien' => 'required|exists:sensortypes,id',
                'id_phong' => 'required|exists:rooms,id',
                'id_trangthai' => 'required|exists:statuses,id',
            ], [
                'ma_so.unique' => 'Mã số đã tồn tại.',
                'ma_so.regex' => 'Mã số phải bắt đầu bằng CB.',
                'ten_cam_bien.required' => 'Tên cảm biến không được để trống.',
                'ma_so.required' => 'Mã số không được để trống.',
                'id_loaicambien.required' => 'Loại cảm biến không được để trống.',
                'id_phong.required' => 'Phòng không được để trống.',
                'id_trangthai.required' => 'Trạng thái không được để trống.',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $sensor = Sensor::create($request->all());

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thêm cảm biến mới: ' . $sensor->ten_cam_bien,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('sensors.index')
                ->with([
                    "success" => "Thêm cảm biến thành công.",
                    "title" => "Thêm cảm biến"
                ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi thêm mới.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }

    /**
     * Lấy thông tin cảm biến để chỉnh sửa
     */
    public function edit($id)
    {
        try {
            // Get the sensor by ID
            $sensor = Sensor::findOrFail($id);
            $sensorTypes = SensorType::all();
            $rooms = Room::all();
            $statuses = Status::all();

            // Handle AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'id' => $sensor->id,
                    'ten_cam_bien' => $sensor->ten_cam_bien,
                    'ma_so' => $sensor->ma_so,
                    'id_loaicambien' => $sensor->id_loaicambien,
                    'id_phong' => $sensor->id_phong,
                    'id_trangthai' => $sensor->id_trangthai
                ]);
            }

            $sensors = Sensor::all();
            
            // Return modal view with sensors data
            return view('sensors.partials.modal', compact('sensors', 'sensorTypes', 'rooms', 'statuses'));
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
     * Cập nhật thông tin cảm biến
     */
    public function update(Request $request, $id)
    {
        try {
            $sensor = Sensor::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ten_cam_bien' => 'required|string|max:255',
                'ma_so' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:sensors,ma_so,' . $sensor->id,
                    'regex:/^CB.*$/' // Validate that ma_so starts with 'CB'
                ],
                'id_loaicambien' => 'required|exists:sensortypes,id',
                'id_phong' => 'required|exists:rooms,id', 
                'id_trangthai' => 'required|exists:statuses,id',
            ], [
                'ma_so.unique' => 'Mã số đã tồn tại.',
                'ma_so.regex' => 'Mã số phải bắt đầu bằng CB.',
                'ten_cam_bien.required' => 'Tên cảm biến không được để trống.',
                'ma_so.required' => 'Mã số không được để trống.',
                'id_loaicambien.required' => 'Loại cảm biến không được để trống.',
                'id_phong.required' => 'Phòng không được để trống.',
                'id_trangthai.required' => 'Trạng thái không được để trống.',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $sensor->update([
                'ten_cam_bien' => $request->ten_cam_bien,
                'ma_so' => $request->ma_so,
                'id_loaicambien' => $request->id_loaicambien,
                'id_phong' => $request->id_phong,
                'id_trangthai' => $request->id_trangthai
            ]);

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin cảm biến: ' . $sensor->ten_cam_bien,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('sensors.index')
                ->with([
                    'success' => 'Đã cập nhật thông tin cảm biến thành công.',
                    'title' => 'Cập nhật'
                ]);

        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi cập nhật.'
                ], 500);
            }

            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi cập nhật.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }

    /**
     * Xóa cảm biến
     */
    public function destroy($id)
    {
        try {
            $sensor = Sensor::findOrFail($id);
            $sensorName = $sensor->ten_cam_bien;
            $sensor->delete();

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa cảm biến: ' . $sensorName,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('sensors.index')
                ->with([
                    "success" => "Đã xóa cảm biến thành công.",
                    "title" => "Xóa"
                ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi xóa.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Import cảm biến từ file Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('file');
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row if exists
            if (isset($rows[0]) && is_array($rows[0])) {
                array_shift($rows);
            }

            $successCount = 0;
            $errorCount = 0;

            foreach ($rows as $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Validate and create record
                    $tenCamBien = trim($row[0] ?? '');
                    $maSo = trim($row[1] ?? '');
                    $loaiCamBien = trim($row[2] ?? '');
                    $phong = trim($row[3] ?? '');
                    $trangThai = trim($row[4] ?? '');
                    
                    if (empty($tenCamBien)) {
                        throw new \Exception('Tên cảm biến không được để trống');
                    }
                    if (empty($maSo)) {
                        throw new \Exception('Mã số không được để trống');
                    }
                    if (empty($loaiCamBien)) {
                        throw new \Exception('Loại cảm biến không được để trống');
                    }
                    if (empty($phong)) {
                        throw new \Exception('Phòng không được để trống');
                    }
                    if (empty($trangThai)) {
                        throw new \Exception('Trạng thái không được để trống');
                    }

                    // Tìm hoặc tạo loại cảm biến
                    $sensorType = SensorType::firstOrCreate(
                        ['ten_loai_cam_bien' => $loaiCamBien],
                        ['mo_ta' => '']
                    );

                    // Tìm phòng
                    $room = Room::firstOrCreate(
                        ['ten_phong' => $phong],
                        ['mo_ta' => '']
                    );
                    if (!$room) {
                        throw new \Exception('Không tìm thấy phòng: ' . $phong);
                    }

                    // Tìm trạng thái
                    $status = Status::where('ten_trang_thai', $trangThai)->first();
                    if (!$status) {
                        throw new \Exception('Không tìm thấy trạng thái: ' . $trangThai);
                    }

                    // Tạo hoặc cập nhật cảm biến
                    Sensor::create([
                        'ma_so' => $maSo,
                        'ten_cam_bien' => $tenCamBien,
                        'id_loaicambien' => $sensorType->id,
                        'id_phong' => $room->id,
                        'id_trangthai' => $status->id
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                }
            }

            // Log the import action
            SystemLog::create([
                'noi_dung_thuc_hien' => "Import cảm biến từ file Excel. Thành công: $successCount, Lỗi: $errorCount",
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('sensors.index')->with([
                'success' => "Thành công: $successCount; Thất bại: $errorCount",
                'title' => 'Import cảm biến',
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi import file.',
                'title' => 'Lỗi'
            ]);
        }
    }
    /**
     * Xuất mẫu import cảm biến
     */
    public function exportTemplate() 
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $headers = ['Tên cảm biến (*)', 'Mã số (*)', 'Loại cảm biến (*)', 'Phòng (*)', 'Trạng thái (*)'];

            foreach ($headers as $index => $header) {
                $sheet->setCellValue(chr(65 + $index) . '1', $header);
            }

            $columnWidths = [
                'A' => 40,
                'B' => 30,
                'C' => 40,
                'D' => 40,
                'E' => 30
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
            
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'Cảm biến nhiệt độ');
            $sheet->setCellValue('B2', 'CB001');
            $sheet->setCellValue('C2', 'Nhiệt độ');
            $sheet->setCellValue('D2', 'Phòng máy A0101');
            $sheet->setCellValue('E2', 'Hoạt động');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $filename = 'mau_import_cam_bien_' . date('Y-m-d') . '.xlsx';
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
}