<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR {{ $title }}</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f6f1e8;color:#321f16;display:grid;place-items:center;min-height:100vh;margin:0}
        .card{background:#fff;text-align:center;padding:36px;border-radius:24px;box-shadow:0 12px 40px #321f1622;max-width:360px}
        img{width:280px;height:280px;max-width:100%} h1{margin:0 0 8px} p{color:#6b5b51} button{border:0;border-radius:999px;padding:12px 24px;background:#7b3f24;color:#fff;font-weight:bold;cursor:pointer}
        @media print{body{background:#fff}.card{box-shadow:none}button{display:none}}
    </style>
</head>
<body><div class="card"><h1>{{ $title }}</h1><p>امسح الكود لعرض القائمة وإرسال الطلب</p>
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=560x560&data={{ urlencode($url) }}" alt="QR {{ $title }}">
    <p>{{ $url }}</p><button onclick="window.print()">طباعة الكود</button></div></body>
</html>
