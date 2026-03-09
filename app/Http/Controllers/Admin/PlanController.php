<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plans\StorePlanRequest;
use App\Http\Requests\Admin\Plans\UpdatePlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::query()
            ->ordered()
            ->withCount('companies')
            ->paginate(15)
            ->withQueryString();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $featureLabels = SubscriptionService::featureLabels();
        return view('admin.plans.create', compact('featureLabels'));
    }

    public function store(StorePlanRequest $request)
    {
        $data = $request->validated();
        $data['features'] = array_values($data['features'] ?? []);
        SubscriptionPlan::create($data);
        SubscriptionService::invalidatePlansCache();

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('plans.plan_created'));
    }

    public function edit(SubscriptionPlan $plan)
    {
        $featureLabels = SubscriptionService::featureLabels();
        return view('admin.plans.edit', compact('plan', 'featureLabels'));
    }

    public function update(UpdatePlanRequest $request, SubscriptionPlan $plan)
    {
        $data = $request->validated();
        $data['features'] = array_values($data['features'] ?? []);
        $plan->update($data);
        SubscriptionService::invalidatePlansCache();

        return redirect()
            ->route('admin.plans.index')
            ->with('success', __('plans.plan_updated'));
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->companies()->exists()) {
            return back()->with('error', __('plans.cannot_delete_has_companies'));
        }
        $plan->delete();
        SubscriptionService::invalidatePlansCache();
        return back()->with('success', __('plans.plan_deleted'));
    }

    public function toggle(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        SubscriptionService::invalidatePlansCache();
        return back()->with('success', $plan->is_active ? __('plans.plan_activated') : __('plans.plan_deactivated'));
    }
}
