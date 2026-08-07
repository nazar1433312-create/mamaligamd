<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Перенаправление на оплату...</title>
</head>
<body>
    <p>Перенаправляем на страницу оплаты LiqPay...</p>
    <form id="liqpay-form" action="{{ $url }}" method="POST" accept-charset="utf-8">
        <input type="hidden" name="data" value="{{ $data }}">
        <input type="hidden" name="signature" value="{{ $signature }}">
    </form>
    <script>document.getElementById('liqpay-form').submit();</script>
</body>
</html>
