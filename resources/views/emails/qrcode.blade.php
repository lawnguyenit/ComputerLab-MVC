<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã QR Code của bạn</title>
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
            display: inline-block !important;
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
        <img src="{{ asset('qrcodes/1746280814.png') }}" alt="Mã QR Code">

    </div>
    <div class="content">
        <h2>Xin chào <span class="highlight">{{ $user->ho_ten }}</span>,</h2>
        
        <p>Dưới đây là mã QR Code chứa mật khẩu của bạn:</p>
        <div class="qr-container">
            <h3>Mã QR Code</h3>
            <div class="qr-code">
                <img src="{{ asset('qrcodes/1746280814.png') }}" alt="Mã QR Code">
            </div>
        </div>
        
        <p>Bạn có thể sử dụng mã QR Code này để đăng nhập nhanh vào hệ thống hoặc chia sẻ với các ứng dụng hỗ trợ.</p>
        
        <p><strong>Lưu ý:</strong> Vui lòng không chia sẻ mã QR Code này với người khác để đảm bảo an toàn thông tin.</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Hệ thống Quản lý Phòng máy. Tất cả các quyền được bảo lưu.</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>