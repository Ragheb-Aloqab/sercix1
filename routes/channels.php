<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    return $user instanceof \App\Models\Company && (int) $user->id === (int) $companyId;
});

Broadcast::channel('App.Models.Company.{id}', function ($user, $id) {
    return $user instanceof \App\Models\Company && (int) $user->id === (int) $id;
});
