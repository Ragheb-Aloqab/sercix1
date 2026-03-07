<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * Only applies when a tenant is bound (company or subdomain context).
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!app()->bound('tenant')) {
            return;
        }

        $tenant = app('tenant');

        if (!$tenant || !isset($tenant->id)) {
            return;
        }

        $builder->where($model->getTable() . '.company_id', $tenant->id);
    }
}
