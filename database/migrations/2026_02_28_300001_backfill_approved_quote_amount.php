<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('maintenance_requests')
            ->whereNotNull('approved_quotation_id')
            ->whereNull('approved_quote_amount')
            ->select('id', 'approved_quotation_id')
            ->get();

        foreach ($rows as $row) {
            $price = DB::table('quotations')->where('id', $row->approved_quotation_id)->value('price');
            if ($price !== null) {
                DB::table('maintenance_requests')->where('id', $row->id)->update(['approved_quote_amount' => $price]);
            }
        }
    }

    public function down(): void
    {
        // No rollback - data is denormalized
    }
};
