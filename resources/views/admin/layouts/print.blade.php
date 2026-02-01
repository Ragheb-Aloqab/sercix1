<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاتورة</title>

    {{-- Tailwind --}}
    @vite(['resources/css/app.css'])

    <style>
        /* للطباعة */
        @media print {
            body {
                background: white !important;
            }

            /* أي عنصر يحمل هذا الكلاس لن يظهر عند الطباعة */
            .no-print {
                display: none !important;
            }
        }

        /* إزالة هوامش المتصفح ومنع ظهور رابط الصفحة أو التاريخ */
        @page {
            margin: 0;
        }
    </style>
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
