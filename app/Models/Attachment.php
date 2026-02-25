<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',           // before_photo | after_photo | signature | other | driver_invoice | quotation_invoice
        'file_path',
        'maintenance_invoice_pdf_path',  // CamScanner-style PDF generated from image
        'original_name',
        'file_size',
        'uploaded_by',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
