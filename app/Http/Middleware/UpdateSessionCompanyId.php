<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateSessionCompanyId
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::guard('company')->check()) {
            $sessionId = $request->session()->getId();
            $companyId = Auth::guard('company')->id();
            if ($sessionId && $companyId) {
                DB::table(config('session.table', 'sessions'))
                    ->where('id', $sessionId)
                    ->update(['company_id' => $companyId]);
            }
        }

        return $response;
    }
}
