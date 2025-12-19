<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Alert</title>
</head>
<body>
    <h2>Canh bao bao mat</h2>
    <p>Co dau hieu can kiem tra he thong log.</p>
    <p>{!! $detail ?? 'Khong co thong tin chi tiet.' !!}</p>
    <p>Loai: {{ $type ?? 'UNKNOWN' }}</p>
</body>
</html>
