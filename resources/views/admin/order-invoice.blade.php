<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>
        *{box-sizing:border-box}html,body{margin:0;padding:0;background:#fff;color:#000;font-family:Tahoma,Arial,sans-serif;font-size:11px}.receipt{width:76mm;margin:0 auto;padding:3mm 2mm}.center{text-align:center}.logo{width:18mm;height:18mm;object-fit:contain;filter:grayscale(1)}h1{font-size:17px;margin:2px 0}p{margin:3px 0}.dash{border:0;border-top:1px dashed #000;margin:8px 0}.meta{display:grid;grid-template-columns:1fr 1fr;gap:4px 8px}.meta div{min-width:0}.meta span{display:block;font-size:9px}.meta b{display:block;overflow-wrap:anywhere}.order-number{font-size:14px;font-weight:bold;direction:ltr}.status{border:1px solid #000;padding:4px;text-align:center;font-weight:bold;margin-top:6px}table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{padding:4px 2px;border-bottom:1px dashed #777;text-align:right;vertical-align:top;overflow-wrap:anywhere}th{font-size:9px;border-top:1px solid #000;border-bottom:1px solid #000}.qty{width:10mm;text-align:center}.price{width:17mm;text-align:left}.totals{margin-top:5px}.row{display:flex;justify-content:space-between;padding:2px 0}.grand{font-size:15px;font-weight:bold;border-top:1px solid #000;border-bottom:1px double #000;margin-top:4px;padding:6px 0}.notes{margin-top:7px;border:1px dashed #000;padding:5px}.footer{text-align:center;margin-top:10px;font-size:9px}.print-button{display:block;width:72mm;margin:8px auto;padding:9px;border:1px solid #000;background:#fff;color:#000;font-weight:bold;cursor:pointer}@media print{@page{size:80mm auto;margin:0}html,body{width:80mm}.receipt{margin:0;padding:3mm 2mm}.print-button{display:none}}
    </style>
</head>
<body onload="setTimeout(() => window.print(), 250)">
    <main class="receipt">
        <header class="center">
            <img class="logo" src="{{ asset('assets/images/logo2026.png') }}" alt="كافيه زون">
            <h1>كافيه زون</h1>
            <p>فاتورة الطلب</p>
            <hr class="dash">
            <span>رقم الطلب</span>
            <div class="order-number">{{ $order->order_number }}</div>
            <div class="status">{{ $order->statusMessage() }}</div>
        </header>

        <hr class="dash">
        <section class="meta">
            <div><span>التاريخ</span><b>{{ $order->created_at->format('d/m/Y h:i A') }}</b></div>
            <div><span>نوع الطلب</span><b>{{ ['dine_in'=>'داخل الكافي','takeaway'=>'استلام','delivery'=>'توصيل'][$order->order_type] ?? $order->order_type }}</b></div>
            @if($order->customer_name)<div><span>الزبون</span><b>{{ $order->customer_name }}</b></div>@endif
            @if($order->customer_phone)<div><span>الهاتف</span><b dir="ltr">{{ $order->customer_phone }}</b></div>@endif
            @if($order->diningTable)<div><span>الطاولة</span><b>{{ $order->diningTable->name }}</b></div>@endif
            @if($order->deliveryArea)<div><span>المنطقة</span><b>{{ $order->deliveryArea->name }}</b></div>@endif
        </section>
        @if($order->address)<p><b>العنوان:</b> {{ $order->address }}</p>@endif
        <p><b>الدفع:</b> {{ $order->payment_details['name'] ?? '—' }} — {{ ['pending'=>'بانتظار التأكيد','confirmed'=>'مؤكد','rejected'=>'مرفوض','unpaid'=>'غير مدفوع'][$order->payment_status] ?? $order->payment_status }}</p>

        <hr class="dash">
        <table>
            <thead><tr><th>الصنف</th><th class="qty">العدد</th><th class="price">الإجمالي</th></tr></thead>
            <tbody>@foreach($order->items as $item)<tr><td>{{ $item->name }}<small> ({{ number_format((float)$item->price,2) }})</small></td><td class="qty">{{ $item->quantity }}</td><td class="price">{{ number_format((float)$item->total,2) }} ₪</td></tr>@endforeach</tbody>
        </table>

        <section class="totals">
            <div class="row"><span>المجموع</span><span>{{ number_format((float)$order->subtotal,2) }} ₪</span></div>
            @if((float)$order->delivery_fee > 0)<div class="row"><span>التوصيل</span><span>{{ number_format((float)$order->delivery_fee,2) }} ₪</span></div>@endif
            <div class="row grand"><span>الإجمالي</span><span>{{ number_format((float)$order->total,2) }} ₪</span></div>
        </section>
        @if($order->notes)<div class="notes"><b>ملاحظات:</b> {{ $order->notes }}</div>@endif
        <footer class="footer"><hr class="dash"><b>شكراً لزيارتكم</b><p>كافيه زون</p></footer>
    </main>
    <button class="print-button" type="button" onclick="window.print()">طباعة الفاتورة</button>
</body>
</html>
