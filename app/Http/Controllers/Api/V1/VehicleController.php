<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Vehicles\Services\VehicleQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * GET /api/v1/vehicles
     * List company vehicles with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $company = $request->user();

        $perPage = min(max((int) $request->get('per_page', 15), 1), 100);
        $service = new VehicleQueryService($company->id, $perPage);

        $search = $request->string('search')->toString();
        $status = $request->string('status', '')->toString();
        $branchId = $request->filled('branch_id') ? (int) $request->branch_id : null;

        $paginator = $service->paginate($search, $status, $branchId);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
