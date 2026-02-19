<div id="createOrderModal" class="fixed inset-0 hidden z-50 overflow-y-auto">
  <div class="absolute inset-0 bg-black/40 min-h-full"></div>
  <div class="absolute inset-0 flex items-start sm:items-center justify-center p-4 pt-8 sm:pt-4 pb-24">
    <div class="w-full max-w-2xl rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-soft overflow-hidden">
      <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500 dark:text-slate-400">إنشاء</p>
          <h3 class="text-lg font-black">طلب يدوي</h3>
        </div>
        <button id="closeCreateOrder" type="button"
          class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form method="POST" action="#">
        @csrf

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-semibold">اسم العميل</label>
            <input class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none"
              placeholder="مثال: شركة الربيع" />
          </div>
          <div>
            <label class="text-sm font-semibold">رقم الجوال</label>
            <input class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none"
              placeholder="05xxxxxxxx" />
          </div>

          <div>
            <label class="text-sm font-semibold">المركبة</label>
            <input class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none"
              placeholder="مثال: Toyota Camry 2020" />
          </div>

          <div>
            <label class="text-sm font-semibold">الخدمة</label>
            <select class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none">
              <option>تغيير زيت</option>
              <option>تغيير كفرات</option>
              <option>فحص سريع</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold">طريقة الدفع</label>
            <select class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none">
              <option>عند الاستلام</option>
              <option>Tap (Online)</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold">الموقع</label>
            <input class="mt-2 w-full px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none"
              placeholder="حي - شارع - معلم" />
          </div>

          <div class="sm:col-span-2">
            <label class="text-sm font-semibold">ملاحظات</label>
            <textarea rows="3"
              class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent outline-none"
              placeholder="ملاحظة للطاقم..."></textarea>
          </div>
        </div>

        <div class="p-5 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <button type="button" id="cancelCreateOrder"
            class="px-4 py-3 min-h-[44px] rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
            <i class="fa-regular fa-circle-xmark me-2"></i> إلغاء
          </button>
          <button type="submit"
            class="px-4 py-3 min-h-[44px] rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
            <i class="fa-solid fa-floppy-disk me-2"></i> حفظ الطلب
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
