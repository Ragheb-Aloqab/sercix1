@extends('admin.layouts.app')

@section('title', 'لوحة التحكم | SERV.X')
@section('subtitle', 'لوحة التحكم')
@section('page_title', 'نظرة عامة على التشغيل')

@section('content')
  {{-- KPI cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">طلبات اليوم</p>
          <p class="text-3xl font-black mt-1">24</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center">
          <i class="fa-solid fa-receipt"></i>
        </div>
      </div>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">+12% مقارنة بالأمس</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">قيد التنفيذ</p>
          <p class="text-3xl font-black mt-1">7</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center">
          <i class="fa-solid fa-user-gear"></i>
        </div>
      </div>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">فنيون متاحون: 5</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">إيراد تقديري</p>
          <p class="text-3xl font-black mt-1">3,420</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
          <i class="fa-solid fa-sack-dollar"></i>
        </div>
      </div>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">يشمل الدفع عند الاستلام</p>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">مخزون منخفض</p>
          <p class="text-3xl font-black mt-1">3</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
      </div>
      <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">خدمات تحتاج متابعة</p>
    </div>
  </div>

  {{-- Content grid --}}
  <div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-4">
    {{-- Orders table --}}
    <div class="xl:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
      <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-3">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">أحدث الطلبات</p>
          <h2 class="text-lg font-black">قائمة الطلبات</h2>
        </div>

        <div class="flex items-center gap-2">
          <select class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm">
            <option>الكل</option>
            <option>جديد</option>
            <option>قيد التنفيذ</option>
            <option>مكتمل</option>
            <option>ملغي</option>
          </select>
          <button class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
            <i class="fa-solid fa-file-csv me-2"></i> تصدير CSV
          </button>
        </div>
      </div>

      <div class="p-5 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-slate-500 dark:text-slate-400">
            <tr class="text-start">
              <th class="py-3 font-semibold">رقم</th>
              <th class="py-3 font-semibold">العميل</th>
              <th class="py-3 font-semibold">الخدمة</th>
              <th class="py-3 font-semibold">الدفع</th>
              <th class="py-3 font-semibold">الحالة</th>
              <th class="py-3 font-semibold">إجراءات</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
            <tr>
              <td class="py-4 font-bold">#1048</td>
              <td class="py-4">
                <p class="font-semibold">شركة الربيع</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Toyota Camry</p>
              </td>
              <td class="py-4">
                <p class="font-semibold">تغيير زيت + فلتر</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">داخل الرياض</p>
              </td>
              <td class="py-4">
                <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold">
                  عند الاستلام
                </span>
              </td>
              <td class="py-4">
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                  جديد
                </span>
              </td>
              <td class="py-4">
                <div class="flex flex-wrap gap-2">
                  <button class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                    <i class="fa-regular fa-folder-open me-2"></i> فتح
                  </button>
                  <button class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold">
                    <i class="fa-solid fa-user-plus me-2"></i> إسناد فني
                  </button>
                </div>
              </td>
            </tr>
          </tbody>

        </table>
      </div>
    </div>

    {{-- Right column --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
      <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">التوزيع والتتبع</p>
        <h2 class="text-lg font-black">خريطة التشغيل</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
          هنا لاحقًا تربط Google Maps / Mapbox لتظهر مواقع الفنيين والطلبات.
        </p>
      </div>

      <div class="p-5">
        <div class="rounded-3xl h-56 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 border border-slate-200 dark:border-slate-800 relative overflow-hidden">
          <div class="absolute top-6 start-6 flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/80 dark:bg-slate-900/70 backdrop-blur border border-slate-200/70 dark:border-slate-800">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <p class="text-xs font-semibold">فني متاح: 5</p>
          </div>

          <div class="absolute bottom-6 start-6 flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/80 dark:bg-slate-900/70 backdrop-blur border border-slate-200/70 dark:border-slate-800">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            <p class="text-xs font-semibold">طلبات نشطة: 7</p>
          </div>

          <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
              <i class="fa-solid fa-map-location-dot text-2xl text-slate-500 dark:text-slate-300"></i>
              <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Map Placeholder</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">Google Maps / Mapbox Integration</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection
