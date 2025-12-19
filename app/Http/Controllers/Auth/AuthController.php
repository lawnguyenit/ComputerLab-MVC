<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Validator;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Http\Controllers\Auth\Rule;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Giới hạn brute force
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return back()->withErrors([
                'email' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ít phút.'
            ])->onlyInput('email');
        }

        // --------- KIỂM TRA TÀI KHOẢN BỊ KHÓA ----------
        $user = User::where('email', $request->email)->first();

        if ($user && $user->status == 0) {

            // Ghi log khóa login
            SystemLog::create([
                'noi_dung_thuc_hien'  => 'Đăng nhập thất bại - tài khoản đã bị khóa; IP: ' . $request->ip(),
                'id_nguoidung'        => $user->id,
                'thoi_gian_thuc_hien' => now('Asia/Ho_Chi_Minh'),
            ]);

            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
            ]);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            $user = Auth::user();

            SystemLog::create([
                'noi_dung_thuc_hien'  => 'Đăng nhập hệ thống; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
                'id_nguoidung'        => $user->id,
                'thoi_gian_thuc_hien' => now('Asia/Ho_Chi_Minh'),
            ]);

            return redirect()->intended('dashboard')->with([
                'success' => 'Đăng nhập thành công!',
                'title'   => 'Thông báo'
            ]);
        }

        // Sai mật khẩu
        RateLimiter::hit($this->throttleKey($request), 60);

        Log::warning('Login failed', [
            'email' => $request->email,
            'ip'    => $request->ip(),
            'time'  => now()
        ]);

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.'
        ])->onlyInput('email');
    }



    // =======================
    // SUPPORT FUNCTION
    // =======================
    protected function throttleKey(Request $request)
    {
        return strtolower($request->email) . '|' . $request->ip();
    }

    /**
     * Đăng xuất người dùng
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Đăng xuất hệ thống; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
                'id_nguoidung' => Auth::id(),
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);
        }
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Hiển thị form quên mật khẩu
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Xử lý yêu cầu đặt lại mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $key = strtolower($request->email).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Bạn yêu cầu quá nhiều lần. Vui lòng thử lại sau ít phút.'
            ]);
        }

        RateLimiter::hit($key, 60);

        try {

            // ====== KIỂM TRA TRẠNG THÁI TÀI KHOẢN ======
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->with('status', 'Nếu email tồn tại, hệ thống đã gửi liên kết đặt lại mật khẩu');
            }

            if ($user->status != 1) {
                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đang bị khóa hoặc chưa kích hoạt.'
                ]);
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {

                SystemLog::create([
                    'noi_dung_thuc_hien' => 'Yêu cầu đặt lại mật khẩu; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
                    'id_nguoidung' => $user->id,
                    'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                ]);

                return back()->with('status', 'Nếu email tồn tại, hệ thống đã gửi liên kết đặt lại mật khẩu');
            }

            return back()->withErrors([
                'email' => 'Không thể gửi yêu cầu đặt lại mật khẩu.'
            ]);

        } catch (\Throwable $e) {

            Log::error('Reset password failed', [
                'email' => $request->email,
                'ip'   => $request->ip(),
                'error'=> $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Có lỗi xảy ra. Vui lòng thử lại sau.'
            ]);
        }
    }


    /**
     * Hiển thị form đặt lại mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Xử lý đặt lại mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
   public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()      // có chữ hoa + chữ thường
                    ->letters()        // có chữ cái
                    ->numbers()        // có số
                    ->symbols(),       // có ký tự đặc biệt
            ],
        ]);
        try {

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) use ($request) {

                    // Cập nhật mật khẩu
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    // Đăng xuất các phiên cũ (tùy chọn – khuyến cáo)
                    if (method_exists(auth()->guard(), 'logoutOtherDevices')) {
                        Auth::logoutOtherDevices($password);
                    }

                    // ===== GHI LOG =====
                    SystemLog::create([
                        'noi_dung_thuc_hien' => 'Đặt lại mật khẩu; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
                        'id_nguoidung' => $user->id,
                        'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                    ]);

                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', 'Mật khẩu đã được cập nhật.')
                : back()->withErrors(['email' => __($status)]);

        } catch (\Throwable $e) {

            Log::error('Password reset error', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'message' => $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ]);
        }
    }

    /**
     * Hiển thị form đổi mật khẩu
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('auth.resert-password');
    }

    /**
     * Xử lý đổi mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();

        // Nếu muốn chắc chắn truy vấn DB → hạn chế lỗi user session không đồng bộ
        $user = User::findOrFail($user->id);

        // Không đổi nếu mật khẩu mới giống mật khẩu cũ
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại!']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60), // buộc logout các session khác
        ]);

        // Ghi log bảo mật
        SystemLog::create([
            'noi_dung_thuc_hien' => 'Thay đổi mật khẩu; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
            'id_nguoidung' => $user->id,
            'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

        // Nếu muốn bảo mật cao → logout toàn bộ session khác
        Auth::logoutOtherDevices($request->password);

        return back()->with('status', 'Mật khẩu đã được thay đổi thành công!');
    }


    /**
     * Hiển thị trang hồ sơ cá nhân
     */
    public function showProfile()
    {
        $user = Auth::user();
        $khoas = Department::all();
        return view('auth.profile', compact('user', 'khoas')) 
        -> with([
            'success' => 'Truy cập thành công!',
            'title' => 'Thông báo'
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân
     */
    public function updateProfile(Request $request)
    {
        // Get current authenticated user
        $user = Auth::user();

        // Create validator instance with custom messages
            $validator = Validator::make($request->all(), [
            'ten_tai_khoan' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'ten_tai_khoan')->ignore($user->id)
            ],
            'ho_ten' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)
            ],
            'sdt' => 'nullable|regex:/^[0-9\-\+\(\)\s]*$/|min:10|max:15',
            'id_khoa' => 'nullable|integer|exists:departments,id',
            // chỉ validate khi có nhập password
            'password' => 'nullable|confirmed|min:8',
        ], [
            'ten_tai_khoan.required' => 'Tên tài khoản không được để trống',
            'ten_tai_khoan.unique' => 'Tên tài khoản đã được sử dụng',
            'ho_ten.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã được sử dụng',
            'sdt.regex' => 'Số điện thoại không hợp lệ',

            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp'
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            $user = User::find($user->id);

            // Update user basic info
            $user->ten_tai_khoan = $request->ten_tai_khoan;
            $user->ho_ten = $request->ho_ten;
            $user->email = $request->email;
            $user->sdt = $request->sdt;
            $user->id_khoa = $request->id_khoa;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Log the profile update
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin cá nhân; IP: ' . $request->ip() . '; Browser: ' . $request->userAgent(),
                'id_nguoidung' => $user->id,
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            DB::commit();

            return redirect()->route('profile')->with([
                'success' => 'Cập nhật thông tin cá nhân thành công!',
                'title' => 'Thông báo'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            Log::error('Profile update error: ' . $e->getMessage());

            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi cập nhật thông tin! Vui lòng thử lại sau.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }
}