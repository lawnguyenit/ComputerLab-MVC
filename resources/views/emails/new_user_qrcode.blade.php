<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin đăng nhập - Mã QR Code</title>
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
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .info-table th, .info-table td {
            padding: 12px;
            border: 1px solid #e3e6f0;
        }
        .info-table th {
            background-color: #f8f9fc;
            text-align: left;
            width: 40%;
            color: #17a2b8;
        }
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fc;
            border-radius: 8px;
        }
        .qr-code {
            max-width: 300px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .qr-code img {
            max-width: 100%;
            height: auto;
        }
        .note {
            background-color: #f8f9fc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            margin: 20px 0;
        }
        .highlight {
            color: #17a2b8;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #858796;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <span>&#128187;</span>
        </div>
        <h1>Hệ thống Quản lý Phòng máy</h1>
    </div>
    <div class="content">
        <h2>Xin chào <span class="highlight">{{ $user->ho_ten }}</span>,</h2>
        
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
        
        <div class="qr-container">
            <h3>Mã QR Code đăng nhập</h3>
            <p>Quét mã QR Code dưới đây để đăng nhập nhanh vào hệ thống:</p>
            <div class="qr-code">
                <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code đăng nhập">
            </div>
        </div>
        
        <p>Vui lòng đăng nhập vào hệ thống bằng thông tin trên và đổi mật khẩu ngay sau khi đăng nhập lần đầu.</p>
        
        <div class="note">
            <strong>Lưu ý:</strong> Đây là email tự động, vui lòng không trả lời email này. Nếu bạn không yêu cầu tạo tài khoản này, vui lòng liên hệ với quản trị viên hệ thống.
        </div>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Hệ thống Quản lý Phòng máy. Tất cả các quyền được bảo lưu.</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>