<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ComputerRoomController;
use App\Http\Controllers\RoomRestrictionScheduleController;
use App\Http\Controllers\RoomManagerController;
use App\Http\Controllers\DataListController;
use App\Http\Controllers\AccessRoomController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\DeviceRestrictionScheduleController;

use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\SystemLogController;
use App\Models\RoomManager;

Route::get('/', function () {
    return view('auth.login');
});

// Auth Routes
Route::controller(AuthController::class)->group(function () {
    // Login Routes
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login');
    Route::post('logout', 'logout')->name('logout');
    
    // Password Reset Routes
    Route::get('forgot-password', 'showForgotPasswordForm')->name('password.request');
    Route::post('forgot-password', 'forgotPassword')->name('password.email');
    Route::get('reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('reset-password', 'resetPassword')->name('password.update');
    
    // Change Password Routes (yêu cầu đăng nhập)
    Route::middleware('auth')->group(function () {
        Route::get('change-password', 'showChangePasswordForm')->name('password.change');
        Route::post('change-password', 'changePassword')->name('password.change.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Route::get('/dashboard', [AuthController::class, 'showDashboard'])->name('dashboard');
    Route::get('/home', [AuthController::class, 'showDashboard'])->name('home');
    Route::get('/profile', [AuthController::class,'showProfile'])->name('profile');
    Route::post('/profile', [AuthController::class,'updateProfile'])->name('profile.update');

    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/store', [RoomController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [RoomController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [RoomController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [RoomController::class, 'destroy'])->name('destroy');
        Route::post('/import', [RoomController::class, 'import'])->name('import');
        Route::get('/export-template', [RoomController::class, 'exportTemplate'])->name('template');
    });

    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('index');
        Route::get('/create', [DeviceController::class, 'create'])->name('create');
        Route::post('/store', [DeviceController::class,'store'])->name('store');
        Route::get('/edit/{id}', [DeviceController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [DeviceController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [DeviceController::class, 'destroy'])->name('destroy');   
        Route::post('/import', [DeviceController::class, 'import'])->name('import');
        Route::get('/export-template', [DeviceController::class, 'exportTemplate'])->name('template');
    });

    Route::prefix('sensors')->name('sensors.')->group(function () {
        Route::get('/', [SensorController::class, 'index'])->name('index');
        Route::get('/create', [SensorController::class, 'create'])->name('create');
        Route::post('/store', [SensorController::class,'store'])->name('store');
        Route::get('/edit/{id}', [SensorController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [SensorController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [SensorController::class, 'destroy'])->name('destroy');
        Route::post('/import', [SensorController::class, 'import'])->name('import');
        Route::get('/export-template', [SensorController::class, 'exportTemplate'])->name('template');


    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/store', [UserController::class,'store'])->name('store');
        Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::delete('/block/{id}', [UserController::class, 'block'])->name('block');
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::get('/export-template', [UserController::class, 'exportTemplate'])->name('template');    
    });

    Route::prefix('room-restrictions')->name('room-restrictions.')->group(function () {
        Route::get('/', [RoomRestrictionScheduleController::class, 'index'])->name('index');
        Route::post('/store', [RoomRestrictionScheduleController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [RoomRestrictionScheduleController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [RoomRestrictionScheduleController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [RoomRestrictionScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/import', [RoomRestrictionScheduleController::class, 'import'])->name('import');
        Route::get('/export-template', [RoomRestrictionScheduleController::class, 'exportTemplate'])->name('template');
    });

    Route::prefix('room-managers')->name('room-managers.')->group(function () {
        Route::get('/', [RoomManagerController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [RoomManagerController::class, 'edit'])->name('edit');
        Route::post('/store', [RoomManagerController::class, 'store'])->name('store');
        Route::put('/update/{id}', [RoomManagerController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [RoomManagerController::class, 'destroy'])->name('destroy');
        Route::get('/export', [RoomManagerController::class, 'export'])->name('template');
        Route::post('/import', [RoomManagerController::class, 'import'])->name('import');
    });

    Route::prefix('device-restrictions')->name('device-restrictions.')->group(function () {
        Route::get('/', [DeviceRestrictionScheduleController::class, 'index'])->name('index');
        Route::post('/store', [DeviceRestrictionScheduleController::class,'store'])->name('store');
        Route::get('/edit/{id}', [DeviceRestrictionScheduleController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [DeviceRestrictionScheduleController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [DeviceRestrictionScheduleController::class, 'destroy'])->name('destroy');
        Route::post('/import', [DeviceRestrictionScheduleController::class, 'import'])->name('import');
        Route::get('/export-template', [DeviceRestrictionScheduleController::class, 'exportTemplate'])->name('template');
    });

    Route::prefix('access-room')->name('access-room.')->group(function () {
        Route::get('/', [AccessRoomController::class, 'index'])->name('index');
        Route::post('/access', [AccessRoomController::class,'access'])->name('access');
        Route::post('/close', [AccessRoomController::class, 'close'])->name('close');
        Route::post('/control', [AccessRoomController::class, 'control'])->name('control');
    });

    Route::prefix('admin-room')->name('admin-room.')->group(function () {
        Route::get('/', [AdminRoomController::class, 'index'])->name('index');
        // Thêm vào trong nhóm Route middleware auth/admin
        Route::post('/admin/security-scan', [App\Http\Controllers\AdminRoomController::class, 'scanIntegrity'])->name('admin.security_scan');
    });

    Route::prefix('datalist')->name('datalist.')->middleware(['auth'])->group(function () {
        Route::get('/', [DataListController::class, 'index'])->name('index');
    });
    
    Route:: get('/giaodien', function () {
        return view('tam');
    });
  
    // QR Code Routes
    Route::get('/qrcode', [App\Http\Controllers\QRCodeController::class, 'index'])->name('qrcode.index');
    Route::post('/qrcode/generate', [App\Http\Controllers\QRCodeController::class, 'generate'])->name('qrcode.generate');
    Route::get('/qrcode/list', [App\Http\Controllers\QRCodeController::class, 'list'])->name('qrcode.list');
    Route::delete('/qrcode/delete/{filename}', [App\Http\Controllers\QRCodeController::class, 'delete'])->name('qrcode.delete');
    Route::post('/qrcode/send', [App\Http\Controllers\QrCodeController::class, 'sendQrCode'])->name('qrcode.send');
    Route::post('/process-qr', 'QrCodeController@processQrCode')->name('process.qr');
    Route::get('/weather', [App\Http\Controllers\WeatherController::class, 'index'])->name('weather.index');
});


