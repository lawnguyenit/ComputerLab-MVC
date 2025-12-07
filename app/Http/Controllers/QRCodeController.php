<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class QRCodeController extends Controller
{
    /**
     * Hiển thị form tạo mã QR
     */
    public function index()
    {
        return view('qrcode.index');
    }

    /**
     * Tạo mã QR từ văn bản sử dụng GD Library
     */
    public function generate(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'size' => 'nullable|integer|min:100|max:500',
        ]);

        $text = $request->input('text');
        $size = $request->input('size', 300);

        // Create QR code directory if not exists
        $qrDirectory = public_path('keyqrcode');
        if (!File::exists($qrDirectory)) {
            File::makeDirectory($qrDirectory, 0755, true);
        }
        
        // Tạo tên file duy nhất
        $filename = 'qr-' . Str::random(10) . '.svg';
        $path = $qrDirectory . DIRECTORY_SEPARATOR . $filename;
        
        try {
            // Tạo mã QR sử dụng GD Library
            QrCode::format('svg')
                ->size($size)
                ->errorCorrection('H')
                ->margin(1)
                ->backgroundColor(255, 255, 255) // Màu nền trắng
                ->color(0, 0, 0) // Màu mã QR đen
                ->encoding('UTF-8') // Hỗ trợ tiếng Việt
                ->generate($text,  $path);

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'filename' => $filename
            ]);
           
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo mã QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị danh sách mã QR đã tạo
     */
    public function list()
    {
        $qrCodes = [];
        $files = File::files(public_path('keyqrcode'));
        
        foreach ($files as $file) {
            $qrCodes[] = [
                'filename' => $file->getFilename(),
                'path' => asset('keyqrcode/' . $file->getFilename()),
                'created_at' => date('d/m/Y H:i:s', $file->getCTime())
            ];
        }
        
        // Sắp xếp theo thời gian tạo mới nhất
        usort($qrCodes, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return view('qrcode.list', compact('qrCodes'));
    }

    /**
     * Xóa mã QR
     */
    public function delete($filename)
    {
        $path = public_path('keyqrcode/' . $filename);
        
        if (File::exists($path)) {
            File::delete($path);
            return redirect()->route('qrcode.list')->with('success', 'Mã QR đã được xóa thành công');
        }
        
        return redirect()->route('qrcode.list')->with('error', 'Không tìm thấy mã QR');
    }
}