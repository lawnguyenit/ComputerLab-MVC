<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thông Báo Cháy Khẩn Cấp</title>
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
            background-color: #ff0000;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            text-align: center;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 2px solid #ff0000;
        }
        .warning {
            color: #ff0000;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .details {
            margin: 20px 0;
        }
        .actions {
            background-color: #fff3f3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CẢNH BÁO CHÁY KHẨN CẤP</h1>
        </div>
        
        <div class="content">
            <div class="warning">
                Phát hiện cháy trong phòng máy tính!
            </div>
            
            <div class="details">
                
                <p><strong>Thông tin chi tiết:</strong></p>
                <ul>
                    <li>Vị trí: {{ $location ?? 'Phòng máy tính' }}</li>
                    <li>Thời gian phát hiện: {{ $time ?? now() }}</li>
                    <li>Mức độ cảnh báo: Cao</li>
                </ul>
            </div>
            
            <div class="actions">
                <p><strong>Yêu cầu hành động ngay lập tức:</strong></p>
                <ol>
                    <li>Sơ tán khỏi phòng ngay lập tức</li>
                    <li>Thực hiện theo quy trình sơ tán khẩn cấp</li>
                    <li>Liên hệ với dịch vụ cứu hỏa nếu chưa được thông báo</li>
                    <li>Không quay lại phòng cho đến khi được phép của cơ quan chức năng</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>Đây là cảnh báo khẩn cấp tự động. Vui lòng thực hiện ngay các biện pháp ứng phó.</p>
            <p>Số điện thoại khẩn cấp: {{ $emergency_contact ?? '114' }}</p>
        </div>
    </div>
</body>
</html>
