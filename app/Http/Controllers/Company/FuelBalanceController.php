<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\FuelPaymentTransaction;
use App\Models\FuelRefill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FuelBalanceController extends Controller
{
    public function index()
    {
        $company = auth('company')->user();
        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number', 'name', 'make', 'model', 'fuel_balance']);

        $totalBalance = $vehicles->sum('fuel_balance');

        // Calculate remaining duration until expiry based on previous consumption
        $avgDailyConsumption = $this->getAvgDailyConsumption($company->id);
        $remainingDays = $avgDailyConsumption > 0
            ? (int) floor($totalBalance / $avgDailyConsumption)
            : null;

        return view('company.fuel-balance.index', compact(
            'company',
            'vehicles',
            'totalBalance',
            'remainingDays',
            'avgDailyConsumption'
        ));
    }

    public function addBalance(Request $request)
    {
        $company = auth('company')->user();
        $data = $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999'],
            'payment_method' => ['required', 'string', 'in:bank_transfer'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $vehicle = $company->vehicles()->findOrFail($data['vehicle_id']);

        $receiptPath = null;
        $receiptOriginal = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptPath = $file->store('fuel-receipts/' . $company->id, 'public');
            $receiptOriginal = $file->getClientOriginalName();
        }

        $amount = (float) $data['amount'];
        $vehicle->increment('fuel_balance', $amount);

        FuelPaymentTransaction::create([
            'company_id' => $company->id,
            'vehicle_id' => $vehicle->id,
            'amount' => $amount,
            'payment_method' => $data['payment_method'],
            'receipt_path' => $receiptPath,
            'receipt_path_original' => $receiptOriginal,
            'reference_number' => 'FPT-' . $company->id . '-' . now()->format('YmdHis'),
        ]);

        return redirect()->route('company.fuel-balance')
            ->with('success', number_format($amount, 2) . ' ' . __('company.sar') . ' ' . __('fleet.add_balance') . ' ' . __('common.added'));
    }

    public function addBalanceAll(Request $request)
    {
        $company = auth('company')->user();
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:999999'],
            'payment_method' => ['required', 'string', 'in:bank_transfer'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $vehicles = $company->vehicles()
            ->where('is_active', true)
            ->get();

        if ($vehicles->isEmpty()) {
            return back()->with('error', __('vehicles.no_vehicles'));
        }

        $amount = (float) $data['amount'];
        $perVehicle = round($amount / $vehicles->count(), 2);

        $receiptPath = null;
        $receiptOriginal = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptPath = $file->store('fuel-receipts/' . $company->id, 'public');
            $receiptOriginal = $file->getClientOriginalName();
        }

        $ref = 'FPT-ALL-' . $company->id . '-' . now()->format('YmdHis');
        foreach ($vehicles as $vehicle) {
            $vehicle->increment('fuel_balance', $perVehicle);
            FuelPaymentTransaction::create([
                'company_id' => $company->id,
                'vehicle_id' => $vehicle->id,
                'amount' => $perVehicle,
                'payment_method' => $data['payment_method'],
                'receipt_path' => $receiptPath,
                'receipt_path_original' => $receiptOriginal,
                'reference_number' => $ref,
            ]);
        }

        return redirect()->route('company.fuel-balance')
            ->with('success', __('fleet.add_balance_all') . ' — ' . number_format($amount, 2) . ' ' . __('company.sar') . ' (' . $vehicles->count() . ' ' . __('fleet.my_vehicles') . ')');
    }

    private function getAvgDailyConsumption(int $companyId): float
    {
        $last30Days = FuelRefill::where('company_id', $companyId)
            ->where('refilled_at', '>=', now()->subDays(30))
            ->selectRaw('SUM(cost) as total_cost')
            ->first();

        $total = (float) ($last30Days->total_cost ?? 0);
        return $total > 0 ? $total / 30 : 0;
    }
}
