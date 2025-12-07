<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceType;
use App\Models\Room;
use App\Models\Status;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\DeviceRestrictionSchedule;

class DeviceController extends Controller
{
    /**
     * Hiển thị danh sách thiết bị
     */
    public function index()
    {
        $devices = Device::with(['deviceType', 'room', 'status'])->get();
        $deviceTypes = DeviceType::all();
        $rooms = Room::all();
        $statuses = Status::all();

        $khoathietbi = DeviceRestrictionSchedule::where(function($query) {
            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
            $query->where('thoi_gian_bat_dau', '<=', $now)
                  ->where(function($q) use ($now) {
                      $q->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                  });
        })->get();

        return view('devices.index', compact('devices', 'deviceTypes', 'rooms','statuses', 'khoathietbi'));
            
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ten_thiet_bi' => 'required|string|max:255',
                'ma_so' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:devices',
                    'regex:/^TB.*$/' // Validate that ma_so starts with 'TB'
                ],
                'id_loaithietbi' => 'required|exists:devicetypes,id',
                'id_phong' => 'required|exists:rooms,id', 
                'id_trangthai' => 'required|exists:statuses,id',
                'nguong_dieu_khien' => 'nullable|numeric'
            ], [
                'ten_thiet_bi.required' => 'Tên thiết bị không được để trống',
                'ma_so.required' => 'Mã số không được để trống',
                'ma_so.regex' => 'Mã số phải bắt đầu bằng TB',
                'id_loaithietbi.required' => 'Loại thiết bị không được để trống',
                'id_phong.required' => 'Phòng không được để trống',
                'id_trangthai.required' => 'Trạng thái không được để trống',
                'ten_thiet_bi.max' => 'Tên thiết bị không được vượt quá 255 ký tự',
                'ma_so.max' => 'Mã số không được vượt quá 50 ký tự',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $device = Device::create($request->all());

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thêm thiết bị mới: ' . $device->ten_thiet_bi,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('devices.index')
                ->with([
                    "success" => "Thêm thiết bị thành công.",
                    "title" => "Thêm thiết bị"
                ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi thêm thiết bị: ' . $e->getMessage(),
                    'title' => 'Lỗi'
                ])
                ->withInput();
        }
    }

    
  
    public function edit($id)
    {
        // Get the device by ID
        $device = Device::findOrFail($id);
        $deviceTypes = DeviceType::all();
        $rooms = Room::all();
        $statuses = Status::all();

        // Handle AJAX request
        if (request()->ajax()) {
            return response()->json([
                'id' => $device->id,
                'ten_thiet_bi' => $device->ten_thiet_bi,
                'ma_so' => $device->ma_so,
                'id_loaithietbi' => $device->id_loaithietbi,
                'id_phong' => $device->id_phong,
                'id_trangthai' => $device->id_trangthai,
                'nguong_dieu_khien' => $device->nguong_dieu_khien,
            ]);
        }

        $devices = Device::all();
        
        // Return modal view with devices data
        return view('devices.partials.modal', compact('devices', 'deviceTypes', 'rooms', 'statuses'));
    }

    /**
     * Cập nhật thông tin thiết bị
     */
    public function update(Request $request, $id)
    {
        try {
            $device = Device::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ten_thiet_bi' => 'required|string|max:255',
                'ma_so' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:devices,ma_so,' . $device->id,
                    'regex:/^TB.*$/' // Validate that ma_so starts with 'TB'
                ],
                'id_loaithietbi' => 'required|exists:devicetypes,id',
                'id_phong' => 'required|exists:rooms,id',
                'id_trangthai' => 'required|exists:statuses,id',
                'nguong_dieu_khien' => 'nullable|numeric'
            ], [
                'ten_thiet_bi.required' => 'Tên thiết bị không được để trống',
                'ma_so.required' => 'Mã số không được để trống',
                'ma_so.regex' => 'Mã số phải bắt đầu bằng TB',
                'id_loaithietbi.required' => 'Loại thiết bị không được để trống',
                'id_phong.required' => 'Phòng không được để trống',
                'id_trangthai.required' => 'Trạng thái không được để trống',
                'ten_thiet_bi.max' => 'Tên thiết bị không được vượt quá 255 ký tự',
                'ma_so.max' => 'Mã số không được vượt quá 50 ký tự',
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

            $device->update([
                'ten_thiet_bi' => $request->ten_thiet_bi,
                'ma_so' => $request->ma_so,
                'id_loaithietbi' => $request->id_loaithietbi,
                'id_phong' => $request->id_phong,
                'id_trangthai' => $request->id_trangthai,
                'nguong_dieu_khien' => $request->nguong_dieu_khien,
            ]);

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin thiết bị: ' . $device->ten_thiet_bi,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('devices.index')
                ->with([
                    'success' => 'Cập nhật thiết bị thành công!',
                    'title' => 'Cập nhật thiết bị'
                ]);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi cập nhật thiết bị!'
                ], 500);
            }

            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi cập nhật thiết bị!',
                    'title' => 'Lỗi'
                ]);
        }
    }

    /**
     * Xóa thiết bị
     */
    public function destroy($id)
    {
        try {
            $device = Device::findOrFail($id);
            $deviceName = $device->ten_thiet_bi;
            $device->delete();

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa thiết bị: ' . $deviceName,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('devices.index')
                ->with([
                    "success" => "Xóa thiết bị thành công.",
                    "title" => "Xóa thiết bị"
                ]);
        } catch (\Exception $e) {
            return redirect()->route('devices.index')
                ->with([
                    "error" => "Có lỗi xảy ra khi xóa thiết bị.",
                    "title" => "Lỗi"
                ]);
        }
        
    }

    /**
     * Import thiết bị từ file Excel
     */
    public function import(Request $request)
    {
        try {
            // Validate file upload
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('file');
            
            // Check if file exists and is readable
            if (!$file || !$file->isValid()) {
                throw new \Exception('File không hợp lệ hoặc bị lỗi');
            }

            $reader = IOFactory::createReader('Xlsx');
            
            // Verify file is readable Excel format
            if (!$reader->canRead($file->getPathname())) {
                throw new \Exception('Không thể đọc file Excel. Vui lòng kiểm tra định dạng file');
            }

            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Check if file has data
            if (count($rows) <= 1) {
                throw new \Exception('File không có dữ liệu để import');
            }

            // Remove header row if exists
            if (isset($rows[0]) && is_array($rows[0])) {
                array_shift($rows);
            }

            $successCount = 0;
            $errorCount = 0;

            foreach ($rows as $index => $row) {
                try {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $tenThietBi = trim($row[0] ?? '');
                    $maSo = trim($row[1] ?? '');
                    $loaiThietBi = trim($row[2] ?? '');
                    $phong = trim($row[3] ?? '');
                    $trangThai = trim($row[4] ?? '');
                    $nguongDieuKhien = empty(trim($row[5] ?? '')) ? 0 : (float)$row[5];
                    // Validate required fields
                    $requiredFields = [
                        'Tên thiết bị' => $tenThietBi,
                        'Mã số' => $maSo,
                        'Loại thiết bị' => $loaiThietBi,
                        'Phòng' => $phong,
                        'Trạng thái' => $trangThai,
                    ];

                    foreach ($requiredFields as $field => $value) {
                        if (empty($value)) {
                            throw new \Exception("$field không được để trống");
                        }
                    }

                    // Validate nguong_dieu_khien
                    if (!is_numeric($nguongDieuKhien)) {
                        throw new \Exception("Ngưỡng điều khiển phải là số");
                    }

                    // Check if device code already exists
                    $existingDevice = Device::where('ma_so', $maSo)->first();
                    if ($existingDevice) {
                        throw new \Exception("Mã số '$maSo' đã tồn tại trong hệ thống");
                    }

                    // Find or create device type
                    $deviceType = DeviceType::firstOrCreate(
                        ['ten_loai_thiet_bi' => $loaiThietBi],
                        ['mo_ta' => '']
                    );

                    // Find room
                    $room = Room::where('ten_phong', $phong)->first();
                    if (!$room) {
                        throw new \Exception("Không tìm thấy phòng: '$phong'");
                    }

                    // Find status
                    $status = Status::where('ten_trang_thai', $trangThai)->first();
                    if (!$status) {
                        throw new \Exception("Không tìm thấy trạng thái: '$trangThai'");
                    }

                    // Create device
                    Device::create([
                        'ten_thiet_bi' => $tenThietBi,
                        'ma_so' => $maSo,
                        'id_loaithietbi' => $deviceType->id,
                        'id_phong' => $room->id,
                        'id_trangthai' => $status->id,
                        'nguong_dieu_khien' => $nguongDieuKhien,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                }
            }

            // Log the import action
            SystemLog::create([
                'noi_dung_thuc_hien' => "Import thiết bị từ file Excel. Thành công: $successCount, Lỗi: $errorCount",
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('devices.index')->with([
                'success' => "Thành công: $successCount; Thất bại: $errorCount",
                'title' => 'Import thiết bị'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('devices.index')->with([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'title' => 'Lỗi import'
            ]);
        }
    }

    /**
     * Xuất mẫu import thiết bị
     */
    public function exportTemplate() 
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $headers = ['Tên thiết bị (*)', 'Mã số (*)', 'Loại thiết bị (*)', 'Phòng (*)', 'Trạng thái (*)', 'Ngưỡng điểu khiển (*)'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        // Add example data
        $exampleData = ['Máy tính Dell', 'TB001', 'Máy tính', 'P.101', 'Hoạt động', '0'];

        foreach ($exampleData as $index => $value) {
            $sheet->setCellValue(chr(65 + $index) . '2', $value);
        }

        // Style the header row
        $headerStyle = $sheet->getStyle('A1:F1');
        $headerStyle->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="mau_import_thiet_bi_'. date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Save to PHP output
        $writer->save('php://output');
        exit;
    }
}