<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MqttDataController;

// ... existing code ...

// Bỏ middleware auth:api để dễ dàng kiểm tra
Route::post('/mqtt-data', [MqttDataController::class, 'handleMqttData']);