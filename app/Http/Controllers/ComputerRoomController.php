<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Device;

class ComputerRoomController extends Controller
{
    /**
     * Hiển thị trang quản lý phòng máy tính
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rooms= Room::all();
        

        return view('computerroom.index', compact('rooms', 'roomsByBuilding'));
    }

    /**
     * Hiển thị chi tiết một phòng máy tính
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $room = Room::with(['devices' => function($query) {
            $query->where('type', 'computer')->orWhere('type', 'laptop');
        }])->findOrFail($id);

        // Đếm số lượng thiết bị theo trạng thái
        $room->total_devices = $room->devices->count();
        $room->available_devices = $room->devices->where('status', 'available')->count();
        $room->occupied_devices = $room->devices->where('status', 'occupied')->count();
        $room->maintenance_devices = $room->devices->where('status', 'maintenance')->count();

        return view('computerroom.show', compact('room'));
    }

    /**
     * Cập nhật trạng thái của một phòng máy tính
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $room->status = $request->status;
        $room->save();

        return redirect()->route('computerroom.index')->with('success', 'Cập nhật trạng thái phòng thành công');
    }

    /**
     * Lấy dữ liệu phòng máy tính theo AJAX
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoomsData()
    {
        $rooms = Room::with(['devices' => function($query) {
            $query->where('type', 'computer')->orWhere('type', 'laptop');
        }])->get();

        foreach ($rooms as $room) {
            $room->total_devices = $room->devices->count();
            $room->available_devices = $room->devices->where('status', 'available')->count();
            $room->occupied_devices = $room->devices->where('status', 'occupied')->count();
            $room->maintenance_devices = $room->devices->where('status', 'maintenance')->count();
            
            // Xác định trạng thái phòng
            if ($room->total_devices == 0) {
                $room->status = 'empty';
            } elseif ($room->available_devices == 0) {
                $room->status = 'occupied';
            } elseif ($room->maintenance_devices > 0 && $room->maintenance_devices == $room->total_devices) {
                $room->status = 'maintenance';
            } else {
                $room->status = 'available';
            }
        }

        return response()->json($rooms);
    }
}