<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeviceTypeController extends Controller
{
    /**
     * Hiển thị danh sách loại thiết bị
     */
    public function index()
    {
        $deviceTypes = DeviceType::all();
        return view('device-types.index', compact('deviceTypes'));
    }

    /**
     * Hiển thị form tạo loại thiết bị mới
     */
    public function create()
    {
        return view('device-types.create');
    }

    /**
     * Lưu loại thiết bị mới vào database
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_loai_thiet_bi' => 'required|string|max:255|unique:devicetypes',
            'mo_ta' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $deviceType = DeviceType::create($request->all());

        // Ghi log
        SystemLog::create([
            'noi_dung_thuc_hien' => 'Thêm loại thiết bị mới: ' . $deviceType->ten_loai_thiet_bi,
            'id_nguoidung' => Auth::id(),
            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        return redirect()->route('device-types.index')
            ->with([
                "success" => "Thêm loại thiết bị thành công.",
                "title" => "Thêm loại thiết bị"
            ]);
    }

    /**
     * Hiển thị form chỉnh sửa loại thiết bị
     */
    public function edit($id)
    {
        // Get the device type by ID
        $deviceType = DeviceType::findOrFail($id);

        // Handle AJAX request
        if (request()->ajax()) {
            return response()->json([
                'id' => $deviceType->id,
                'ten_loai_thiet_bi' => $deviceType->ten_loai_thiet_bi,
                'mo_ta' => $deviceType->mo_ta
            ]);
        }

        $deviceTypes = DeviceType::all();
        
        // Return modal view with device types data
        return view('device-types.partials.modal', compact('deviceTypes'));
    }

    /**
     * Cập nhật thông tin loại thiết bị
     */
    public function update(Request $request, $id)
    {
        try {
            $deviceType = DeviceType::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ten_loai_thiet_bi' => 'required|string|max:255|unique:devicetypes,ten_loai_thiet_bi,' . $deviceType->id,
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

            $deviceType->update([
                'ten_loai_thiet_bi' => $request->ten_loai_thiet_bi,
                'mo_ta' => $request->mo_ta
            ]);

            // Ghi log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin loại thiết bị: ' . $deviceType->ten_loai_thiet_bi,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('device-types.index')
                ->with([
                    'success' => 'Cập nhật loại thiết bị thành công!',
                    'title' => 'Cập nhật loại thiết bị'
                ]);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi cập nhật loại thiết bị!'
                ], 500);
            }

            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi cập nhật loại thiết bị!',
                    'title' => 'Lỗi'
                ]);
        }
    }

    /**
     * Xóa loại thiết bị
     */
    public function destroy($id)
    {
        $deviceType = DeviceType::findOrFail($id);
        
        // Kiểm tra xem loại thiết bị có đang được sử dụng không
        if ($deviceType->devices()->count() > 0) {
            return redirect()->route('device-types.index')
                ->with([
                    'error' => 'Không thể xóa loại thiết bị này vì đang có thiết bị sử dụng!',
                    'title' => 'Lỗi'
                ]);
        }
        
        $deviceTypeName = $deviceType->ten_loai_thiet_bi;
        $deviceType->delete();

        // Ghi log
        SystemLog::create([
            'noi_dung_thuc_hien' => 'Xóa loại thiết bị: ' . $deviceTypeName,
            'id_nguoidung' => Auth::id(),
            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        return redirect()->route('device-types.index')
            ->with([
                "success" => "Xóa loại thiết bị thành công.",
                "title" => "Xóa loại thiết bị"
            ]);
    }
}