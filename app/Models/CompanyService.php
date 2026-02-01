<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyService extends Model
{
     use HasFactory;
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
