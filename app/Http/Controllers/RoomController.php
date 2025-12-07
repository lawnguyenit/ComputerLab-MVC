<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RoomsImport;
use App\Models\RoomRestrictionSchedule;
use App\Models\RoomUsageHistory;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RoomController extends Controller
{
    /**
     * Hiển thị danh sách phòng
     */
    public function index()
    {
        $rooms = Room::all();
        $sudungphong = RoomUsageHistory::where(function($query) {
            $now = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
            $query->where('thoi_gian_bat_dau', '<=', $now)
                  ->where(function($q) use ($now) {
                      $q->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                  });
        })->get();
        $khoaphong = RoomRestrictionSchedule::where(function($query) {
            $now = now()->setTimezone('Asia/Ho_Chi_Minh');
            $query->where('thoi_gian_bat_dau', '<=', $now)
                  ->where(function($q) use ($now) {
                      $q->whereNull('thoi_gian_ket_thuc')
                        ->orWhere('thoi_gian_ket_thuc', '>=', $now);
                  });
        })->get();
        return view('rooms.index', compact('rooms', 'sudungphong', 'khoaphong'));
    }

    
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ten_phong' => 'required|string|max:255|unique:rooms',
                'khu_vuc' => 'required|string|max:255',
                'vi_tri' => 'required|string|max:255',
                'mo_ta' => 'nullable|string',
            ], [
                'ten_phong.unique' => 'Tên phòng đã tồn tại trong hệ thống.'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
    
            $room = Room::create($request->all());
    
            // Log the action
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thêm phòng mới: ' . $room->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);
    
            return redirect()->route('rooms.index')
                ->with([
                    "success" => "Thêm phòng thành công.",
                    "title" => "Thêm phòng"
                ]);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating room: ' . $e->getMessage());
            
            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi thêm phòng.',
                    'title' => 'Lỗi'
                ])
                ->withInput();
        }
    }

    /**
     * Hiển thị thông tin chi tiết phòng
     */
    public function show(Room $room)
    {
        return view('rooms.show', compact('room'));
    }

    /**
     * Hiển thị form chỉnh sửa phòng
     */
    public function edit($id)
    {
        // Get the room by ID
        $room = Room::findOrFail($id);

        // Handle AJAX request
        if (request()->ajax()) {
            return response()->json([
                'id' => $room->id,
                'ten_phong' => $room->ten_phong,
                'khu_vuc' => $room->khu_vuc, 
                'vi_tri' => $room->vi_tri,
                'mo_ta' => $room->mo_ta
            ]);
        }

        $rooms = Room::all();
        
        // Return modal view with rooms data
        return view('rooms.partials.modal', compact('rooms'));
    }

    /**
     * Cập nhật thông tin phòng
     */
    public function update(Request $request, $id)
    {
        try {
            $room = Room::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ten_phong' => 'required|string|max:255|unique:rooms,ten_phong,' . $room->id,
                'khu_vuc' => 'required|string|max:255',
                'vi_tri' => 'required|string|max:255',
                'mo_ta' => 'nullable|string',
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

            $room->update([
                'ten_phong' => $request->ten_phong,
                'khu_vuc' => $request->khu_vuc,
                'vi_tri' => $request->vi_tri,
                'mo_ta' => $request->mo_ta
            ]);

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin phòng: ' . $room->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

          

            return redirect()->route('rooms.index')
                ->with([
                    'success' => 'Cập nhật phòng thành công!',
                    'title' => 'Cập nhật phòng'
                ]);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi cập nhật phòng!'
                ], 500);
            }

            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi cập nhật phòng!',
                    'title' => 'Lỗi'
                ]);
        }
    }

    /**
     * Xóa phòng
     */
    public function destroy($id)
    {
        try {
            // Kiểm tra xem phòng có đang được sử dụng không
            $room = Room::findOrFail($id);
            $roomName = $room->ten_phong;
            $room->delete();

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa phòng: ' . $roomName,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('rooms.index')
                ->with([
                    "success" => "Xóa phòng thành công.",
                    "title" => "Xóa phòng"
                ]);

        } catch (\Exception $e) {
            return redirect()->route('rooms.index')
                ->with([
                    "error" => "Có lỗi xảy ra khi xóa phòng.",
                    "title" => "Lỗi"
                ]);
        }
    }

  
    public function import(Request $request)
    {
        // Validate file upload
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
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate and create record
                $tenPhong = trim($row[0] ?? '');
                $khuVuc = trim($row[1] ?? '');
                $viTri = trim($row[2] ?? '');
                $moTa = trim($row[3] ?? '');
                
                if (empty($tenPhong)) {
                    throw new \Exception('Tên phòng không được để trống');
                }
                if (empty($khuVuc)) {
                    throw new \Exception('Khu vực không được để trống');
                }
                if (empty($viTri)) {
                    throw new \Exception('Vị trí không được để trống');
                }

                Room::firstOrCreate(
                    ['ten_phong' => $tenPhong],
                    [
                        'khu_vuc' => $khuVuc,
                        'vi_tri' => $viTri,
                        'mo_ta' => $moTa
                    ]
                );

                $successCount++;

            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Dòng " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        // Log the import action
        SystemLog::create([
            'noi_dung_thuc_hien' => "Import phòng từ file Excel. Thành công: $successCount, Lỗi: $errorCount",
            'id_nguoidung' => Auth::id(),
            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        $message = "Thành công: $successCount; Thất bại: $errorCount";
        
        return redirect()->route('rooms.index')->with([
            'success' => $message,
            'title' => 'Import phòng'
        ]);
    }

    function exportTemplate() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $headers = ['Tên phòng (*)', 'Khu vực (*)', 'Vị trí (*)', 'Mô tả'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        // Add example data
        $exampleData = ['P.101', 'Tầng 1', 'Dãy A', 'Phòng máy tính'];

        foreach ($exampleData as $index => $value) {
            $sheet->setCellValue(chr(65 + $index) . '2', $value);
        }

        // Style the header row
        $headerStyle = $sheet->getStyle('A1:D1');
        $headerStyle->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Set headers for download
        $name = 'mau_import_phong_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $name. '"');
        header('Cache-Control: max-age=0');

        // Save to PHP output
        $writer->save('php://output');
        exit;
    }


}