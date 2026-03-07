<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;

trait BelongsToCompany
{
    /**
     * Boot the trait: add global scope and set company_id on create.
     */
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (app()->bound('tenant') && !$model->company_id) {
                $model->company_id = app('tenant')->id;
            }
        });
    }

    /**
     * Company relationship.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
