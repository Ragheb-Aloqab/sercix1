<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBranch extends Model
{
    use HasFactory;

    protected $table = 'company_branches';

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'city',
        'district',
        'address',
        'latitude',
        'longitude',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // المركبات التابعة للفرع
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_branch_id');
    }

    // الطلبات التابعة للفرع
    public function orders()
    {
        return $this->hasMany(Order::class, 'company_branch_id');
    }
  
}
