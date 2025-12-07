<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('department', 'permissions')->get();
            $departments = Department::all();
            $permissions = Permission::all();
            $userpermissions = UserPermission::all();
            
            return view('users.index', compact('users', 'departments', 'permissions', 'userpermissions'))
                ->with(['success' => 'Thành công.', 'title' => 'Danh sách người dùng']);
                
        } catch (Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tải danh sách người dùng',
                'title' => 'Lỗi'
            ]);
        }
    }

   

    public function store(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'ten_tai_khoan' => 'required|string|max:255|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'ho_ten' => 'required|string|max:255',
                'sdt' => 'nullable|string|max:20||unique:users',
                'id_khoa' => 'required|exists:departments,id',
                'id_quyen' => 'required|exists:permissions,id'
            ], [
                'ten_tai_khoan.required' => 'Tên tài khoản không được để trống',
                'ten_tai_khoan.unique' => 'Tên tài khoản đã tồn tại',
                'email.required' => 'Email không được để trống',
                'email.unique' => 'Email đã tồn tại',
                'email.email' => 'Email không hợp lệ',
                'email.max' => 'Email không được vượt quá 255 ký tự',
                'ho_ten.required' => 'Họ tên không được để trống',
                'ho_ten.max' => 'Họ tên không được vượt quá 255 ký tự',
                'sdt.max' => 'Số điện thoại không được vượt quá 20 ký tự',
                'sdt.unique' => 'Số điện thoại đã được sử dụng',
                'id_khoa.required' => 'Khoa không được để trống',
                'id_khoa.exists' => 'Khoa không tồn tại',
                'id_quyen.required' => 'Quyền không được để trống',
                'id_quyen.exists' => 'Quyền không tồn tại',]);

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
            

            // Generate default password
            $defaultPassword = $request->email;

            // Create new user
            $user = User::create([
                'ten_tai_khoan' => $request->ten_tai_khoan,
                'email' => $request->email,
                'password' => Hash::make($defaultPassword),
                'ho_ten' => $request->ho_ten,
                'sdt' => $request->sdt,
                'id_khoa' => $request->id_khoa,
            ]);

            try {
                UserPermission::create([
                    'id_nguoidung' => $user->id,
                    'id_quyen' => $request->id_quyen
                ]);
            } catch (Exception $e) {
                Log::error('Error assigning permission: ' . $e->getMessage());
                return redirect()->back()->with([
                    'error' => 'Có lỗi xảy ra khi phân quyền người dùng',
                    'title' => 'Lỗi'
                ]);             
            }

            // Send account info via email
            try {
                Mail::send('emails.new_user', [
                    'user' => $user,
                    'password' => $defaultPassword
                ], function($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Thông tin tài khoản mới - Hệ thống Quản lý Phòng máy');
                });
            } catch (Exception $mailException) {
                Log::error('Error sending email: ' . $mailException->getMessage());
                return redirect()->back()->with([
                    'error' => 'Có lỗi xảy ra khi gửi mail về người dùng',
                    'title' => 'Lỗi'
                ]);        
            }

            // Log the action
            try {
                SystemLog::create([
                    'noi_dung_thuc_hien' => 'Thêm người dùng mới: ' . $user->ho_ten,
                    'id_nguoidung' => Auth::id(),
                    'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                ]);
            } catch (Exception $e) {
                Log::error('Error creating system log: ' . $e->getMessage());
                return redirect()->back()->with([
                    'error' => 'Có lỗi xảy ra khi ghi log hệ thống',
                    'title' => 'Lỗi'
                ]); 
            }

            return redirect()->route('users.index')
                ->with([
                    'success' => 'Thêm người dùng thành công!',
                    'title' => 'Thêm người dùng'
                ]);

        } catch (ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with([
                    'error' => 'Dữ liệu không hợp lệ',
                    'title' => 'Lỗi'
                ]);
        } catch (QueryException $e) {
            Log::error('Database error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi thao tác với cơ sở dữ liệu'
                ], 500);
            }
            return redirect()->back()
                ->with([
                    'error' => 'Lỗi khi thao tác với cơ sở dữ liệu',
                    'title' => 'Lỗi'
                ])
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error in store method: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi thêm người dùng'
                ], 500);
            }
            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi thêm người dùng',
                    'title' => 'Lỗi'
                ])
                ->withInput();
        }
    }


    public function edit($id)
    {
        try {
            // Lấy thông tin người dùng
            $user = User::findOrFail($id);
            $departments = Department::all();
            $permissions = Permission::all();
            
            $id_quyen='Chưa cấp quyền';
            if (UserPermission::where('id_nguoidung', $user->id)->exists()) {
                $id_quyen = UserPermission::where('id_nguoidung', $user->id)->first()->id_quyen;
            }

            $tenkhoa= Department::where('id', $user->id_khoa)->first()->ten_khoa;
            // Xử lý AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'id' => $user->id,
                    'ten_tai_khoan' => $user->ten_tai_khoan,
                    'email' => $user->email,
                    'ho_ten' => $user->ho_ten,
                    'sdt' => $user->sdt,
                    'ten_khoa' => $tenkhoa,
                    'id_permissions' => $id_quyen,
                ]);
            }

            $users = User::all();
            
            // Trả về view modal với dữ liệu người dùng
            return view('users.partials.modal', compact('users', 'departments', 'permissions'));
        } catch (Exception $e) {
            Log::error('Error in edit method: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi tải thông tin người dùng'
                ], 500);
            }
            return redirect()->route('users.index')
                ->with([
                    'error' => 'Có lỗi xảy ra khi tải thông tin người dùng',
                    'title' => 'Lỗi'
                ]);
        }
    }


    /**
     * Cập nhật thông tin người dùng
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validate the permission input
            $validator = Validator::make($request->all(), [
                'edit_id_phan_quyen' => 'required|exists:permissions,id',
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

            // Update user permission
            // First remove existing permission
            UserPermission::where('id_nguoidung', $user->id)->delete();
            
            // Add new permission
            UserPermission::create([
                'id_nguoidung' => $user->id,
                'id_quyen' => $request->edit_id_phan_quyen
            ]);

            // Log the permission update
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật quyền người dùng: ' . $user->ho_ten,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật quyền người dùng thành công!'
                ]);
            }

            return redirect()->route('users.index')
                ->with([
                    'success' => 'Cập nhật quyền người dùng thành công!',
                    'title' => 'Cập nhật quyền'
                ]);

        } catch (ValidationException $e) {
            Log::error('Validation error in update method: ' . json_encode($e->errors()));
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with([
                    'error' => 'Dữ liệu không hợp lệ',
                    'title' => 'Lỗi'
                ]);
        } catch (QueryException $e) {
            Log::error('Database error in update method: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi thao tác với cơ sở dữ liệu'
                ], 500);
            }
            return redirect()->back()
                ->with([
                    'error' => 'Lỗi khi thao tác với cơ sở dữ liệu',
                    'title' => 'Lỗi'
                ])
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error in update method: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi cập nhật quyền người dùng!'
                ], 500);
            }

            return redirect()->back()
                ->with([
                    'error' => 'Có lỗi xảy ra khi cập nhật quyền người dùng!',
                    'title' => 'Lỗi'
                ]);
        }
    }


    public function block($id)
    {
        try {
            // Prevent deleting currently logged in user
            if ($id == Auth::id()) {
                if (request()->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể khóa tài khoản đang đăng nhập'
                    ], 422);
                }
                return redirect()->route('users.index')
                    ->with([
                        'error' => 'Không thể khóa tài khoản đang đăng nhập',
                        'title' => 'Lỗi'
                    ]);
            }
            
            $user = User::findOrFail($id);
            $userName = $user->ho_ten;
            
            // Delete user permissions first
            UserPermission::where('id_nguoidung', $id)->delete();


            // Log the deletion
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Khóa tài khoản người dùng: ' . $userName,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Khóa tài khoản thành công!'
                ]);
            }

            return redirect()->route('users.index')
                ->with([
                    'success' => 'Khoa người dùng thành công!',
                    'title' => 'Xóa người dùng'
                ]);

        } catch (QueryException $e) {
            Log::error('Database error in destroy method: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi thao tác với cơ sở dữ liệu'
                ], 500);
            }
            return redirect()->route('users.index')
                ->with([
                    'error' => 'Lỗi khi thao tác với cơ sở dữ liệu',
                    'title' => 'Lỗi'
                ]);
        } catch (Exception $e) {
            Log::error('Error in destroy method: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi khóa tài khoản người dùng'
                ], 500);
            }

            return redirect()->route('users.index')
                ->with([
                    'error' => 'Có lỗi xảy ra khi khóa tài khoản người dùng!',
                    'title' => 'Lỗi'
                ]);
        }
    }

    public function destroy($id)
    {
        try {
            // Prevent deleting currently logged in user
            if ($id == Auth::id()) {
                if (request()->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể xóa tài khoản đang đăng nhập'
                    ], 422);
                }
                return redirect()->route('users.index')
                    ->with([
                        'error' => 'Không thể xóa tài khoản đang đăng nhập',
                        'title' => 'Lỗi'
                    ]);
            }
            
            $user = User::findOrFail($id);
            $userName = $user->ho_ten;
            
            // Delete user permissions first
            UserPermission::where('id_nguoidung', $id)->delete();
            
            // Delete the user
            $user->delete();

            // Log the deletion
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa tài khoản người dùng: ' . $userName,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Xóa tài khoản thành công!'
                ]);
            }

            return redirect()->route('users.index')
                ->with([
                    'success' => 'Xóa người dùng thành công!',
                    'title' => 'Xóa người dùng'
                ]);

        } catch (QueryException $e) {
            Log::error('Database error in destroy method: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lỗi khi thao tác với cơ sở dữ liệu'
                ], 500);
            }
            return redirect()->route('users.index')
                ->with([
                    'error' => 'Lỗi khi thao tác với cơ sở dữ liệu',
                    'title' => 'Lỗi'
                ]);
        } catch (Exception $e) {
            Log::error('Error in destroy method: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi xóa người dùng'
                ], 500);
            }

            return redirect()->route('users.index')
                ->with([
                    'error' => 'Có lỗi xảy ra khi xóa người dùng',
                    'title' => 'Lỗi'
                ]);
        }
    }

    
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls'
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
            
            array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($rows as $index => $row) {
                try {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $tenTaiKhoan = trim($row[0] ?? '');
                    $email = trim($row[1] ?? '');
                    $hoTen = trim($row[2] ?? '');
                    $sdt = trim($row[3] ?? '');
                    $khoa = trim($row[4] ?? '');
                    $password = trim($row[5] ?? '') ?: 'Password@123';

                    if (empty($tenTaiKhoan) || empty($email) || empty($hoTen) || empty($khoa)) {
                        $errorCount++;
                        continue;
                    }

                    if (User::where('ten_tai_khoan', $tenTaiKhoan)->exists()) {
                        $errorCount++;
                        continue;
                    }

                    if (User::where('email', $email)->exists()) {
                        $errorCount++;
                        continue;
                    }

                    $department = Department::where('ten_khoa', $khoa)
                        ->orWhere('ten_viet_tat', $khoa)
                        ->first();
                        
                    if (!$department) {
                        $errorCount++;
                        continue;
                    }

                    $user = User::create([
                        'ten_tai_khoan' => $tenTaiKhoan,
                        'email' => $email,
                        'ho_ten' => $hoTen, 
                        'sdt' => $sdt,
                        'id_khoa' => $department->id,
                        'password' => Hash::make($password)
                    ]);

                    try {
                        $qrCode = QrCode::format('png')
                            ->size(300)
                            ->generate($user->password);
                        file_put_contents(public_path('khoaqr/' . $user->id . '.png'), $qrCode);
                    } catch (Exception $e) {
                        Log::error('Error creating QR code: ' . $e->getMessage());
                    }

                    try {
                        $defaultPermission = Permission::where('ten_quyen', 'Người dùng')->first();
                        if ($defaultPermission) {
                            UserPermission::create([
                                'id_nguoidung' => $user->id,
                                'id_quyen' => $defaultPermission->id
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('Error assigning permission: ' . $e->getMessage());
                    }

                    try {
                        Mail::send('emails.new_user', [
                            'user' => $user,
                            'password' => $password,
                        ], function($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('Thông tin tài khoản mới - Hệ thống Quản lý Phòng máy');
                        });
                    } catch (Exception $e) {
                        Log::error('Error sending email: ' . $e->getMessage());
                    }

                    $successCount++;

                } catch (Exception $e) {
                    $errorCount++;
                }
            }

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Import người dùng từ file Excel: ' . $successCount . ' thành công, ' . $errorCount . ' lỗi',
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now(),
            ]);

            return redirect()->route('users.index')->with([
                'success' => "Thành công $successCount; Thất bại $errorCount;",
                'title' => 'Import người dùng'
            ]);

        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi import file: ' . $e->getMessage(),
                'title' => 'Lỗi'
            ]);
        }
    }

    public function exportTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers
            $headers = ['Tên tài khoản (*)', 'Email (*)', 'Họ tên (*)', 'Số điện thoại', 'Khoa (*)', 'Mật khẩu'];

            foreach ($headers as $index => $header) {
                $sheet->setCellValue(chr(65 + $index) . '1', $header);
            }

            // Add example data
            $exampleData = ['user001', 'user001@example.com', 'Nguyễn Văn A', '0123456789', 'CNTT', 'Password@123'];

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
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $date = date('d-m-Y');
            header('Content-Disposition: attachment;filename="mau_import_nguoi_dung_' .  date('Y-m-d') . '.xlsx"');
            header('Cache-Control: max-age=0');

            // Save to PHP output
            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            Log::error('Error in exportTemplate method: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi tạo file mẫu',
                'title' => 'Lỗi'
            ]);
        }
    }

}