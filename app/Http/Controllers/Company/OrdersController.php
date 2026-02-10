<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderCancelRequested;
class OrdersController extends Controller
{
    public function index()
    {
        return view('company.orders.index');
    }

    public function show(Order $order)
    {
        $company = auth('company')->user();

        abort_unless((int) $order->company_id === (int) $company->id, 403);

        $order->load([
            'technician:id,name,phone',
            'attachments',
            'payments',
            'invoice',
            'services',
            'vehicle',
        ]);

        return view('company.orders.show', compact('company', 'order'));
    }

    public function create()
    {
        return view('company.orders.create');
    }

    public function store(Request $request)
    {
        $company = auth('company')->user();

        $data = $request->validate([
            'vehicle_id'        => ['required', 'integer'],
            'service_ids'       => ['required', 'array', 'min:1'],
            'service_ids.*'     => ['integer', 'exists:services,id'],
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'payment_method'    => ['required', 'in:cash,tap,bank'],
        ]);

        // ✅ تأكد أن السيارة تابعة للشركة
        abort_unless(
            $company->vehicles()->where('id', $data['vehicle_id'])->exists(),
            403,
            'Invalid vehicle.'
        );

        // ✅ تأكد أن الفرع تابع للشركة
        if (!empty($data['company_branch_id'])) {
            abort_unless(
                $company->branches()->where('id', $data['company_branch_id'])->exists(),
                403,
                'Invalid branch.'
            );
        }

        // ✅ اجلب الخدمات المختارة + تحقق أنها متاحة للشركة
        $services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')
                    ->where('cs.company_id', '=', $company->id);
            })
            ->addSelect([
                'cs.base_price as pivot_base_price',
                'cs.is_enabled as pivot_is_enabled',
            ])
            ->whereIn('services.id', $data['service_ids'])
            ->where(function ($q) {
                $q->whereNull('cs.is_enabled')
                    ->orWhere('cs.is_enabled', 1);
            })
            ->get();

        // ✅ لازم كل الخدمات المختارة تكون متاحة
        abort_unless(
            $services->count() === count($data['service_ids']),
            403,
            'One or more services are not enabled.'
        );

        // ✅ المبلغ من أسعار الشركة (pivot) أو السعر الافتراضي للخدمة للشركات الجديدة
        $amount = (float) $services->sum(fn ($s) => (float) ($s->pivot_base_price ?? $s->base_price ?? 0));

        $order = DB::transaction(function () use ($company, $data, $services, $amount) {

            $order = Order::create([
                'company_id'        => $company->id,
                'vehicle_id'        => $data['vehicle_id'],
              //  'company_branch_id' => $data['company_branch_id'] ?? null,
                'status'            => 'pending',
                'notes'             => $data['notes'] ?? null,
            ]);

            // ✅ ربط الخدمات بالطلب مع أسعار البيفوت (وحدة + إجمالي = total_price)
            $syncData = [];
            foreach ($services as $s) {
                $qty = 1;
                $unitPrice = (float) ($s->pivot_base_price ?? $s->base_price ?? 0);
                $syncData[$s->id] = [
                    'qty'         => $qty,
                    'unit_price'  => $unitPrice,
                    'total_price' => $qty * $unitPrice,
                ];
            }
            $order->services()->sync($syncData);

            // ✅ إنشاء دفعة
            Payment::create([
                'order_id'   => $order->id,
                'company_id' => $company->id,
                'method'     => $data['payment_method'],
                'status'     => 'pending',
                'amount'     => $amount,
            ]);
            
            return $order;
        });

        return redirect()
            ->route('company.orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }
    public function cancel(Order $order)
    {
        $company = auth('company')->user();
        abort_unless((int) $order->company_id === (int) $company->id, 403);

        if ($order->technician_id) {
            return back()->with('error', 'الطلب قيد التنفيذ ولا يمكن إلغاؤه مباشرة.');
        }

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new OrderCancelRequested($order));
        }

        return back()->with('success', 'تم إرسال طلب الإلغاء للمدير.');
    }
}
