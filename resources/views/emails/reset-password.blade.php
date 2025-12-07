<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
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
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <span>&#128187;</span>
        </div>
        <h1>ĐẶT LẠI MẬT KHẨU</h1>
    </div>
    <div class="content">
        <p>Xin chào,</p>
        <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn trên <span class="highlight">Hệ thống quản lý phòng máy</span>.</p>
        <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu của bạn:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Đặt lại mật khẩu</a>
        </div>
        
        <div class="note">
            <p><strong>Lưu ý:</strong> Liên kết đặt lại mật khẩu này sẽ hết hạn sau 60 phút.</p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này.</p>
        </div>
        
        <p>Trân trọng,<br><span class="highlight">Hệ thống quản lý phòng máy</span></p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Hệ thống quản lý phòng máy. Tất cả các quyền được bảo lưu.</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>