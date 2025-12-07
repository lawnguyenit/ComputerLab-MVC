<?php

namespace App\Http\Controllers;

use App\Models\RoomManager;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RoomManagersExport;
use App\Imports\RoomManagersImport;
use App\Models\SystemLog;
use Exception;

class RoomManagerController extends Controller
{
    /**
     * Hiển thị danh sách quản lý phòng
     */
    public function index()
    {
        try {
            $roomManagers = RoomManager::all();
            $rooms = Room::all();
            $users = User::all();
            return view('room_managers.index', compact('roomManagers', 'rooms', 'users'));
        } catch (Exception $e) {
            Log::error('Lỗi khi hiển thị danh sách quản lý phòng: ' . $e->getMessage());
            return redirect()->back() -> with([
                'error' => 'Đã xảy ra lỗi khi hiển thị danh sách quản lý phòng. Vui lòng thử lại sau.',
                'title' => 'Lỗi'
            ]);
        }
    }

    /**
     * Lưu thông tin quản lý phòng mới
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_nguoidung' => 'required|exists:nguoi_dung,id',
                'id_phong' => 'required|exists:phong,id',
                'mo_ta' => 'nullable|string|max:255',
            ], [
                'id_nguoidung.required' => 'Vui lòng chọn người quản lý',
                'id_phong.required' => 'Vui lòng chọn phòng',
                'mo_ta.max' => 'Mô tả không được vượt quá 255 ký tự',
            ]);


            RoomManager::create($request->all());

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thêm quản lý phòng '.Room::where('id', $request->id_phong)->first()->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('room-managers.index')->with([
                'success' => 'Thêm quản lý phòng thành công',
                'title' => 'Thành công'
            ]);
        } catch (Exception $e) {
            Log::error('Lỗi khi thêm quản lý phòng: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Đã xảy ra lỗi khi thêm quản lý phòng. Vui lòng thử lại sau.',
                'title' => 'Lỗi'
            ]);
        }
    }

    function edit($id) {
        try {
            $roomManager = RoomManager::findOrFail($id);
            $rooms = Room::all();
            $users = User::all();
            if (request()->ajax()) {
                return response()->json([
                    'roommanager' => $roomManager
                ]);
            }
            return view('room_managers.edit', compact('roomManager', 'rooms', 'users'));
        } catch (Exception $e) {
            Log::error('Lỗi khi hiển thị thông tin quản lý phòng: '. $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Đã xảy ra lỗi khi hiển thị thông tin quản lý phòng. Vui lòng thử lại sau.',
                'title' => 'Lỗi']);
        }
        
    }

    /**
     * Cập nhật thông tin quản lý phòng
     */
    public function update(Request $request, $id)
    {
        try {
            $roomManager = RoomManager::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'id_nguoidung' => 'required|exists:nguoi_dung,id',
                'id_phong' => 'required|exists:phong,id',
                'mo_ta' => 'nullable|string|max:255',
            ], [
                'id_nguoidung.required' => 'Vui lòng chọn người quản lý',
                'id_nguoidung.exists' => 'Người quản lý không tồn tại',
                'id_phong.required' => 'Vui lòng chọn phòng',
                'id_phong.exists' => 'Phòng không tồn tại',
                'mo_ta.max' => 'Mô tả không được vượt quá 255 ký tự',
            ]);


            $roomManager->update($request->all());
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật quản lý phòng '.Room::where('id', $request->id_phong)->first()->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->format('Y-m-d H:i:s')
            ]);

            return redirect()->route('room-managers.index')->with([
                'success' => 'Cập nhật quản lý phòng thành công',
                'title' => 'Thành công']);
        } catch (Exception $e) {
            Log::error('Lỗi khi cập nhật quản lý phòng: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Đã xảy ra lỗi khi cập nhật quản lý phòng. Vui lòng thử lại sau.',
                'title' => 'Lỗi'])->withInput();
        }
    }

    /**
     * Xóa thông tin quản lý phòng
     */
    public function destroy($id)
    {
        try {
            $roomManager = RoomManager::findOrFail($id);
            $roomManager->delete();

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa quản lý phòng '.Room::where('id', $roomManager->id_phong)->first()->ten_phong,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('room-managers.index')->with([
                'success' => 'Xóa quản lý phòng thành công',
                'title' => 'Thành công']);
        } catch (Exception $e) {
            Log::error('Lỗi khi xóa quản lý phòng: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Đã xảy ra lỗi khi xóa quản lý phòng. Vui lòng thử lại sau.',
                'title' => 'Lỗi']);
        }
    }

    /**
     * Xuất danh sách quản lý phòng ra Excel
     */
    public function export() 
    {
        try {
        } catch (Exception $e) {
            Log::error('Lỗi khi xuất danh sách quản lý phòng: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xuất dữ liệu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Nhập danh sách quản lý phòng từ Excel
     */
    public function import(Request $request) 
    {
        try {
           
            
            return redirect()->route('room-managers.index')->with('success', 'Nhập dữ liệu thành công');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi nhập danh sách quản lý phòng: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi nhập dữ liệu: ' . $e->getMessage());
        }
    }
}