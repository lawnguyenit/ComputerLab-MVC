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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class UserController extends Controller
{
    public function index()
    {
        try {
            // Lấy danh sách user + liên kết
            $users = User::select('id','ten_tai_khoan','ho_ten','email', 'sdt', 'id_khoa')
                ->with([
                    'department:id,ten_khoa',
                    'permissions:id,ten_quyen'
                ])->paginate(10);

            $departments = Department::select('id','ten_khoa')->get();
            $permissions = Permission::select('id','ten_quyen')->get();

            $userpermissions = UserPermission::select('id_nguoidung','id_quyen')->get();

            return view('users.index', compact(
                'users',
                'departments',
                'permissions',
                'userpermissions'
            ));

        } catch (\Throwable $e) {

            Log::error('User index load failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return back()->with([
                'error' => 'Có lỗi xảy ra khi tải danh sách người dùng!'.$e->getMessage(),
                'title' => 'Lỗi'
            ]);
        }
    }

   

    public function store(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'ten_tai_khoan' => 'required|string|max:255|unique:users,ten_tai_khoan',
            'email'        => 'required|string|email|max:255|unique:users,email',
            'ho_ten'       => 'required|string|max:255',
            'sdt'          => 'nullable|string|max:20|unique:users,sdt',
            'id_khoa'      => 'required|exists:departments,id',
            'id_quyen'     => 'required|exists:permissions,id'
        ], [
            'ten_tai_khoan.required' => 'Tên tài khoản không được để trống',
            'ten_tai_khoan.unique'   => 'Tên tài khoản đã tồn tại',
            'email.required'         => 'Email không được để trống',
            'email.unique'           => 'Email đã tồn tại',
            'email.email'            => 'Email không hợp lệ',
            'ho_ten.required'        => 'Họ tên không được để trống',
            'sdt.unique'             => 'Số điện thoại đã được sử dụng',
            'id_khoa.required'       => 'Khoa không được để trống',
            'id_quyen.required'      => 'Quyền không được để trống',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate secure password
            $defaultPassword = Str::random(12);

            // Create user
            $user = User::create([
                'ten_tai_khoan'    => $request->ten_tai_khoan,
                'email'            => $request->email,
                'password'         => Hash::make($defaultPassword),
                'ho_ten'           => $request->ho_ten,
                'sdt'              => $request->sdt,
                'id_khoa'          => $request->id_khoa,
                'status'           => 1
            ]);

            // Assign Permission
            UserPermission::create([
                'id_nguoidung' => $user->id,
                'id_quyen'     => $request->id_quyen
            ]);

            // System log
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thêm người dùng mới: ' . $user->ho_ten .
                    '; IP: ' . request()->ip() .
                    '; Browser: ' . request()->userAgent(),
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now('Asia/Ho_Chi_Minh'),
            ]);

            DB::commit();

            // ======= SEND MAIL (KHÔNG rollback nếu lỗi mail) =======
            try {
                Mail::send('emails.new_user', [
                    'user' => $user,
                    'password' => $defaultPassword
                ], function($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Thông tin tài khoản mới - Hệ thống Quản lý');
                });
            } catch (\Throwable $mailError) {
                Log::warning('Create user success but email failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $mailError->getMessage()
                ]);
            }

            return redirect()->route('users.index')->with([
                'success' => 'Thêm người dùng thành công!',
                'title'   => 'Thêm người dùng'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error creating new user', [
                'error' => $e->getMessage(),
                'ip'    => request()->ip()
            ]);

            return back()->with([
                'error' => 'Có lỗi xảy ra khi thêm người dùng',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }



    public function edit($id)
    {
        try {
            // Load user + dept relationship nếu có
            $user = User::with('department')->findOrFail($id);

            $departments = Department::all();
            $permissions = Permission::all();

            // Lấy quyền (có thì lấy, không thì null)
            $userPermission = UserPermission::where('id_nguoidung', $user->id)->first();
            $id_quyen = $userPermission->id_quyen ?? null;

            // Lấy tên khoa an toàn
            $tenKhoa = $user->department->ten_khoa ?? 'Chưa có khoa';

            // Nếu request AJAX → trả JSON
            if (request()->ajax()) {
                return response()->json([
                    'id' => $user->id,
                    'ten_tai_khoan' => $user->ten_tai_khoan,
                    'email' => $user->email,
                    'ho_ten' => $user->ho_ten,
                    'sdt' => $user->sdt,
                    'ten_khoa' => $tenKhoa,
                    'id_permissions' => $id_quyen,
                ]);
            }

            return view('users.partials.modal', compact(
                'user',
                'departments',
                'permissions',
                'id_quyen'
            ));

        } catch (\Exception $e) {
            Log::error('Error in edit method: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra khi tải thông tin người dùng'
                ], 500);
            }

            return redirect()->route('users.index')->with([
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

            $user->status = 1;
            $user->save();

            // Log the permission update
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật quyền người dùng: ' .$user->id. ' - ' . $user->ho_ten. ' - ID quyền:' . $request->edit_id_phan_quyen,
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
            if ($id == Auth::id()) {
                return $this->errorResponse('Không thể khóa tài khoản đang đăng nhập');
            }

            $user = User::findOrFail($id);

            if ($user->status == 0) {
                return $this->errorResponse('Tài khoản đã bị khóa trước đó');
            }

            DB::beginTransaction();

            // Khóa tài khoản
            $user->status = 0;
            $user->save();

            // Xóa quyền (nếu bạn muốn)
            UserPermission::where('id_nguoidung', $id)->delete();

            // Đăng xuất user khỏi tất cả phiên
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // Log hệ thống
            SystemLog::create([
                'noi_dung_thuc_hien' => "Khóa tài khoản người dùng: {$user->ho_ten} (ID: {$user->id}) | IP: " . request()->ip(),
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            DB::commit();

            return $this->successResponse('Khóa tài khoản thành công!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Database error in block method: ' . $e->getMessage());
            return $this->errorResponse('Lỗi khi thao tác với cơ sở dữ liệu', 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in block method: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi khóa tài khoản người dùng!', 500);
        }
    }

    private function successResponse($message)
    {
        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return redirect()->route('users.index')
            ->with(['success' => $message, 'title' => 'Thành công']);
    }

        private function errorResponse($message, $code = 422)
        {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message
                ], $code);
            }

            return redirect()->route('users.index')
                ->with(['error' => $message, 'title' => 'Lỗi']);
        }


    public function destroy($id)
    {
        try {
            if ($id == Auth::id()) {
                return $this->errorResponse('Không thể xóa tài khoản đang đăng nhập');
            }

            DB::beginTransaction();

            $user = User::findOrFail($id);

            SystemLog::where('id_nguoidung', $id)->delete();
            UserPermission::where('id_nguoidung', $id)->delete();
            $user->delete();

            SystemLog::create([
                'noi_dung_thuc_hien' => 'Xóa người dùng: '. $user->ho_ten,
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now('Asia/Ho_Chi_Minh'),
            ]);

            DB::commit();

            return $this->successResponse('Xóa tài khoản thành công!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Destroy user failed: '.$e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi xóa tài khoản người dùng: ', 500);
        }
    }


    
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows); // bỏ dòng tiêu đề

            $successCount = 0;
            $errorCount = 0;

            foreach ($rows as $index => $row) {

                // bỏ dòng trống
                if (empty(array_filter($row))) continue;

                DB::beginTransaction();

                try {
                    $tenTaiKhoan = trim($row[0] ?? '');
                    $email = strtolower(trim($row[1] ?? ''));
                    $hoTen = trim($row[2] ?? '');
                    $sdt = trim($row[3] ?? '');
                    $khoa = trim($row[4] ?? '');
                    $plainPassword = trim($row[5] ?? '') ?: 'Password@123';

                    // Validate bắt buộc
                    if (!$tenTaiKhoan || !$email || !$hoTen || !$khoa) {
                        $errorCount++;
                        DB::rollBack();
                        continue;
                    }

                    // Check duplicate
                    if (User::where('ten_tai_khoan', $tenTaiKhoan)->exists() ||
                        User::where('email', $email)->exists() ||
                        ($sdt && User::where('sdt', $sdt)->exists())) {

                        $errorCount++;
                        DB::rollBack();
                        continue;
                    }

                    // Find department
                    $department = Department::where('ten_khoa', $khoa)
                        ->orWhere('ten_viet_tat', $khoa)
                        ->first();

                    if (!$department) {
                        $errorCount++;
                        DB::rollBack();
                        continue;
                    }

                    // Create user
                    $user = User::create([
                        'ten_tai_khoan' => $tenTaiKhoan,
                        'email' => $email,
                        'ho_ten' => $hoTen,
                        'sdt' => $sdt,
                        'id_khoa' => $department->id,
                        'password' => Hash::make($plainPassword),
                        'status' => 1, // active
                    ]);

                    /**
                     * ===== QR CODE =====
                     * Nếu muốn lưu password gốc: dùng plainPassword
                     */
                    try {
                        $qrCode = QrCode::format('png')
                            ->size(300)
                            ->generate("User: $tenTaiKhoan | Pass: $plainPassword");

                        if (!file_exists(public_path('khoaqr'))) {
                            mkdir(public_path('khoaqr'), 0777, true);
                        }

                        file_put_contents(
                            public_path("khoaqr/{$user->id}.png"),
                            $qrCode
                        );
                    } catch (\Throwable $e) {
                        Log::error('QR Error: ' . $e->getMessage());
                    }

                    /**
                     * ===== PERMISSION =====
                     */
                    try {
                        $defaultPermission = Permission::where('ten_quyen', 'Người dùng')->first();
                        if ($defaultPermission) {
                            UserPermission::create([
                                'id_nguoidung' => $user->id,
                                'id_quyen' => $defaultPermission->id
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Permission Error: ' . $e->getMessage());
                    }

                    /**
                     * ===== SEND EMAIL =====
                     */
                    try {
                        Mail::send('emails.new_user', [
                            'user' => $user,
                            'password' => $plainPassword
                        ], function($m) use ($user) {
                            $m->to($user->email)
                            ->subject('Thông tin tài khoản mới - Hệ thống');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Mail Error: ' . $e->getMessage());
                    }

                    DB::commit();
                    $successCount++;

                } catch (\Throwable $e) {
                    DB::rollBack();
                    $errorCount++;
                    Log::error("Import row $index failed: " . $e->getMessage());
                }
            }

            SystemLog::create([
                'noi_dung_thuc_hien' => "Import user: $successCount thành công, $errorCount lỗi",
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->route('users.index')->with([
                'success' => "Thành công: $successCount | Thất bại: $errorCount",
                'title' => 'Import người dùng'
            ]);

        } catch (\Throwable $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return back()->with([
                'error' => 'Có lỗi xảy ra khi import file.',
                'title' => 'Lỗi'
            ]);
        }
    }


    public function exportTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'Tên tài khoản (*)', 
                'Email (*)', 
                'Họ tên (*)', 
                'Số điện thoại', 
                'Khoa (*)', 
                'Mật khẩu (để trống = Password@123)'
            ];

            foreach ($headers as $i => $title) {
                $sheet->setCellValue(chr(65 + $i).'1', $title);
            }

            $sheet->fromArray([
                ['user001', 'user001@example.com', 'Nguyễn Văn A', '0123456789', 'CNTT', 'Password@123']
            ], null, 'A2');

            $sheet->freezePane('A2');
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);

            foreach (range('A','F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);

            $filename = 'mau_import_nguoi_dung_' . now()->format('Y-m-d') . '.xlsx';

            return response()->streamDownload(function() use ($writer){
                $writer->save('php://output');
            }, $filename);

        } catch (\Throwable $e) {
            Log::error('Export template error: '.$e->getMessage());

            return back()->with([
                'error' => 'Không thể tạo file mẫu',
                'title' => 'Lỗi'
            ]);
        }
    }


}