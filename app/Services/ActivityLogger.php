<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $action,
        string $subjectType,
        int $subjectId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $actorType = 'system',
        ?int $actorId = null
    ): void {
        // تحديد الفاعل تلقائيًا إن لم يُمرر
        if (Auth::check()) {
            $actorType = 'admin'; // عدلها حسب نظامك
            $actorId = Auth::id();
        }

        Activity::create([
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'action'      => $action,
            'subject_type'=> $subjectType,
            'subject_id'  => $subjectId,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}