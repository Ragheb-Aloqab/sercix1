<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة</title>

    {{-- Tailwind + custom styles (includes print rules) --}}
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-gray-900">

@yield('content')

{{-- طباعة تلقائية (اختياري) --}}
<script>
    window.onload = function () {
        window.print();
    }
</script>
</body>
</html>
