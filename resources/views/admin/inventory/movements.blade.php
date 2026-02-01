@extends('admin.layouts.app')

@section('title', 'لوحة تحكم المدير | SERV.X')
@section('page_title', 'سجل حركة المخزون  ')

@section('content')
<!--<body class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100"><div class="p-6 max-w-7xl mx-auto space-y-6">
  --><!-- Header -->

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <!--<h1 class="text-2xl font-bold">سجل حركات المخزون</h1>
        --><p class="text-2xl py-2 font-bold text-slate-500 dark:text-slate-400">متابعة جميع عمليات الإدخال والإخراج والتعديلات</p>
    </div>

    <div class="flex gap-2">
        <button class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-sm font-semibold shadow">
            تصدير Excel
        </button>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow">
            طباعة
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
        <p class="text-sm text-slate-500">إجمالي الداخل اليوم</p>
        <h2 class="text-xl font-bold text-emerald-600">+{{ $inventoy_transaction->where('type',  'return')
    ->sum('quantity_change');}}</h2>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
        <p class="text-sm text-slate-500">إجمالي الخارج اليوم</p>
        <h2 class="text-xl font-bold text-rose-600">-18</h2>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
        <p class="text-sm text-slate-500">التعديلات</p>
        <h2 class="text-xl font-bold text-amber-500">6</h2>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow">
        <p class="text-sm text-slate-500">آخر حركة</p>
<h2 class="text-xl font-bold">
    {{ \Carbon\Carbon::parse(collect($inventoy_transaction)->max('created_at'))->diffForHumans() }}
</h2>    </div>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow grid md:grid-cols-5 gap-4">
    <input type="text" placeholder="بحث عن صنف..." class="px-3 py-2 rounded-xl border dark:bg-slate-900 dark:border-slate-700">

    <select class="px-3 py-2 rounded-xl border dark:bg-slate-900 dark:border-slate-700">
        <option>نوع الحركة</option>
        <option>دخول</option>
        <option>خروج</option>
        <option>تعديل</option>
    </select>

    <input type="date" class="px-3 py-2 rounded-xl border dark:bg-slate-900 dark:border-slate-700">
    <input type="date" class="px-3 py-2 rounded-xl border dark:bg-slate-900 dark:border-slate-700">

    <button class="bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-xl px-4 py-2 text-sm font-semibold">
        تطبيق
    </button>
</div>

<!-- Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow overflow-x-auto">
    <table class="w-full text-sm text-right">
        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-200">
            <tr>
                <th class="p-3">التاريخ</th>
                <th class="p-3">الصنف</th>
                <th class="p-3">النوع</th>
                <th class="p-3">التغير</th>
                <th class="p-3">الكمية بعد</th>
                <th class="p-3">السعر</th>
                <th class="p-3">الطلب</th>
                <th class="p-3">المستخدم</th>
                <th class="p-3">إجراءات</th>
            </tr>
        </thead>

        <tbody class="divide-y dark:divide-slate-700">
            @foreach($inventoy_transaction as $inv)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700">
                <td class="p-3">{{ $inv->created_at->diffForHumans() }}</td>
                <td class="p-3 font-semibold">زيت محرك</td>
                <td class="p-3">
                    <span class="px-2 py-1 text-xs rounded-lg  {{$inv->quantity_change<0 ? 'text-rose-700 bg-rose-100 ':'text-emerald-700 bg-emerald-100 '}} ">{{$inv->transaction_type}}</span>
                </td>
                <td class="p-3 {{$inv->quantity_change<0 ? 'text-rose-600':'text-emerald-600'}} ">{{$inv->quantity_change}}</td>
                <td class="p-3">{{$inv->new_quantity}}</td>
                <td class="p-3">{{$inv->unit_price}}</td>
                <td class="p-3">{{$inv->related_order_type ?? "-"}}</td>
                <td class="p-3">{{$inv->created_by}}</td>
                <td class="p-3">
                    <button class="px-3 py-1 rounded-lg bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs">عرض</button>
                </td>
            </tr>
            @endforeach
           <!-- <tr class="hover:bg-slate-50 dark:hover:bg-slate-700">
                <td class="p-3">2026-01-29</td>
                <td class="p-3 font-semibold">فلتر هواء</td>
                <td class="p-3">
                    <span class="px-2 py-1 text-xs rounded-lg bg-rose-100 text-rose-700">خروج</span>
                </td>
                <td class="p-3 text-rose-600">-3</td>
                <td class="p-3">12</td>
                <td class="p-3">20$</td>
                <td class="p-3">#152</td>
                <td class="p-3">محمد</td>
                <td class="p-3">
                    <button class="px-3 py-1 rounded-lg bg-slate-900 text-white dark:bg-white dark:text-slate-900 text-xs">عرض</button>
                </td>
            </tr>-->
        </tbody>
    </table>
</div>

@endsection
<!--
</body>
</html>-->