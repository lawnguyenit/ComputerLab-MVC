<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Báo cáo dữ liệu cảm biến</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 24px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Báo cáo dữ liệu cảm biến</h1>
        </div>
        
        <div class="content">
            <p>Kính gửi Quản lý phòng,</p>
            
            <p>Đây là báo cáo tự động về dữ liệu cảm biến của phòng <strong>{{ $room->ten_phong }}</strong> ngày <strong>{{ $date->format('d/m/Y') }}</strong>.</p>
            
            <p>Thông tin phòng:</p>
            <table>
                <tr>
                    <th>Tên phòng</th>
                    <td>{{ $room->ten_phong }}</td>
                </tr>
                <tr>
                    <th>Khu vực</th>
                    <td>{{ $room->khu_vuc }}</td>
                </tr>
                <tr>
                    <th>Vị trí</th>
                    <td>{{ $room->vi_tri }}</td>
                </tr>
            </table>
            
            <p>Chi tiết dữ liệu cảm biến được đính kèm trong file Excel. Vui lòng kiểm tra file đính kèm để xem báo cáo đầy đủ.</p>
            
            <p>Trân trọng,</p>
            <p>Hệ thống quản lý phòng máy</p>
        </div>
        
        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
            <p>&copy; {{ date('Y') }} Hệ thống quản lý phòng máy</p>
        </div>
    </div>
</body>
</html>