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
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Ghi log đăng nhập thành công
            $user = Auth::user();
            
            $systemlog = SystemLog::create([
                'noi_dung_thuc_hien' => 'Đăng nhập hệ thống',
                'id_nguoidung' => $user->id,
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);
            $systemlog->save();

            return redirect()->intended('dashboard') -> with(
            'succes', 'Đăng nhập thành công!',
                'title', 'Thông báo'
            );
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
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
                'noi_dung_thuc_hien' => 'Đăng xuất hệ thống',
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
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email không tồn tại trong hệ thống.'
        ]);

        try {
            // Get user by email
            $user = User::where('email', $request->email)->first();
            
            // Generate password reset token
            $token = Password::createToken($user);
            
            // Gửi email đặt lại mật khẩu sử dụng Mailable class
            Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

            // Log password reset request
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Yêu cầu đặt lại mật khẩu',
                'id_nguoidung' => $user->id,
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
            ]);

            // Return response based on email sending status
            return back()->with('status', 'Kiểm tra email để đặt lại mật khẩu');

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Đã xảy ra lỗi khi xử lý yêu cầu']);
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
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Ghi log đổi mật khẩu
                SystemLog::create([
                    'noi_dung_thuc_hien' => 'Đặt lại mật khẩu',
                    'id_nguoidung' => $user->id,
                    'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
                ]);

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', 'Mật khẩu đã được cập nhật')
                    : back()->withErrors(['email' => [__($status)]]);
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
        $user = User::find($user->id);
        $user->password = Hash::make($request->password);
        $user->save();

            // Log password change
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Thay đổi mật khẩu',
                'id_nguoidung' => $user->id,
                'thoi_gian_thuc_hien' => now()->setTimezone('Asia/Ho_Chi_Minh'),
        ]);

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
        -> with(
            'succes', 'Truy cập thành công!',
                'title', 'Thông báo'
            );
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
            'ten_tai_khoan' => 'required|string|max:255|unique:users,ten_tai_khoan,'.$user->id,
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'sdt' => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'id_khoa' => 'nullable|integer|exists:departments,id',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'ten_tai_khoan.required' => 'Tên tài khoản không được để trống',
            'ten_tai_khoan.unique' => 'Tên tài khoản đã được sử dụng',
            'ho_ten.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã được sử dụng',
            'so_dien_thoai.regex' => 'Số điện thoại không hợp lệ',
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

            $user->save();

            // Log the profile update
            SystemLog::create([
                'noi_dung_thuc_hien' => 'Cập nhật thông tin cá nhân',
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
            \Log::error('Profile update error: ' . $e->getMessage());

            return redirect()->back()->with([
                'error' => 'Có lỗi xảy ra khi cập nhật thông tin! Vui lòng thử lại sau.',
                'title' => 'Lỗi'
            ])->withInput();
        }
    }
}