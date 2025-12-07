<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin tài khoản mới</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fc;
        }
        .header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            letter-spacing: 0.5px;
            color: #f8f9fc;
        }
        .logo {
            margin-bottom: 15px;
            font-size: 40px;
        }
        .content {
            padding: 30px;
            background-color: white;
            border: 1px solid #e3e6f0;
            border-top: none;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .button {
            display: inline-block;
            background: linear-gradient(to right, #17a2b8, #138496);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 50px;
            margin: 25px 0;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(to right, #138496, #17a2b8);
            box-shadow: 0 6px 15px rgba(23, 162, 184, 0.4);
            transform: translateY(-2px);
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #858796;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
        .highlight {
            color: #17a2b8;
            font-weight: bold;
        }
        .note {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            margin: 20px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .info-table th,
        .info-table td {
            padding: 12px 15px;
            border: 1px solid #e3e6f0;
        }
        .info-table th {
            background-color: #f8f9fc;
            font-weight: 600;
            width: 35%;
            color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <span>&#128187;</span>
        </div>
        <h1>HỆ THỐNG QUẢN LÝ PHÒNG MÁY</h1>
    </div>
    <div class="content">
        <p>Xin chào <span class="highlight">{{ $user->ho_ten }}</span>,</p>
        
        <p>Tài khoản của bạn đã được tạo thành công trên <span class="highlight">Hệ thống Quản lý Phòng máy</span>. Dưới đây là thông tin đăng nhập của bạn:</p>
        
        <table class="info-table">
            <tr>
                <th>Tên tài khoản:</th>
                <td>{{ $user->ten_tai_khoan }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Mật khẩu:</th>
                <td>{{ $password }}</td>
            </tr>
        </table>
        
        <p>Vui lòng đăng nhập vào hệ thống bằng thông tin trên và đổi mật khẩu ngay sau khi đăng nhập lần đầu.</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">Đăng nhập ngay</a>
        </div>
        
        <div class="note">
            <p><strong>Lưu ý:</strong> Đây là email tự động, vui lòng không trả lời email này. Nếu bạn không yêu cầu tạo tài khoản này, vui lòng liên hệ với quản trị viên hệ thống.</p>
        </div>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Hệ thống Quản lý Phòng máy. Tất cả các quyền được bảo lưu.</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>