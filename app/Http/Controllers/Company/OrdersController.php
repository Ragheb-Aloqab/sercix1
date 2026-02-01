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
    public function index(Request $request)
    {
        $company = auth('company')->user();
        $status  = $request->string('status')->toString();

        $orders = Order::query()
            ->where('company_id', $company->id)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->with(['technician:id,name,phone'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = [
            'pending',
            'accepted',
            'on_the_way',
            'in_progress',
            'completed',
            'cancelled',
        ];

        return view('company.orders.index', compact('company', 'orders', 'statuses', 'status'));
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
        $company = auth('company')->user();

        // ✅ كل الخدمات + بيانات الشركة (إن وجدت) من company_services
        $services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')
                    ->where('cs.company_id', '=', $company->id);
            })
            ->addSelect([
                'cs.base_price as pivot_base_price',
                'cs.estimated_minutes as pivot_estimated_minutes',
                'cs.is_enabled as pivot_is_enabled',
            ])
            // ✅ المتاح:
            // - شركة جديدة (لا يوجد pivot) => متاح
            // - يوجد pivot => لازم is_enabled = 1
            ->where(function ($q) {
                $q->whereNull('cs.is_enabled')
                    ->orWhere('cs.is_enabled', 1);
            })
            ->orderBy('services.name')
            ->get();

        $branches = $company->branches()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vehicles = $company->vehicles()
            ->orderByDesc('id')
            ->get();

        return view('company.orders.create', compact('company', 'services', 'branches', 'vehicles'));
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

        // ✅ المبلغ من أسعار الشركة (pivot) + لو null اعتبرها 0
        $amount = (float) $services->sum(fn ($s) => (float) ($s->pivot_base_price ?? 0));

        $order = DB::transaction(function () use ($company, $data, $services, $amount) {

            $order = Order::create([
                'company_id'        => $company->id,
                'vehicle_id'        => $data['vehicle_id'],
              //  'company_branch_id' => $data['company_branch_id'] ?? null,
                'status'            => 'pending',
                'notes'             => $data['notes'] ?? null,
            ]);

            // ✅ ربط الخدمات بالطلب
            $order->services()->sync($services->pluck('id')->all());
            //dd();
            // ✅ إنشاء دفعة
            Payment::create([
                'order_id'   => $order->id,
                'company_id' => $company->id,
                'method'     => $data['payment_method'],
                'status'     => 'pending',
                'amount'     => $services->first()->base_price,
            ]);
            
            return $order;
        });

        return redirect()
            ->route('company.orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }
    public function cancel($id)
{
    $order = Order::findOrFail($id);

    if ($order->technician_id) {
        return back()->with('error', 'الطلب قيد التنفيذ ولا يمكن إلغاؤه مباشرة.');
    }

    // جلب المدير
    $admin = User::where('role', 'admin')->first();

    if ($admin) {
        $admin->notify(new OrderCancelRequested($order));
    }

    return back()->with('success', 'تم إرسال طلب الإلغاء للمدير.');
}
}
