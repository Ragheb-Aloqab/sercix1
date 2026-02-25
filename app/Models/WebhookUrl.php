<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookUrl extends Model
{
    protected $fillable = ['name', 'url', 'events', 'company_id', 'is_active', 'secret'];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function dispatch(string $event, array $payload, ?int $companyId = null): void
    {
        $query = static::query()->where('is_active', true)->whereJsonContains('events', $event);
        if ($companyId !== null) {
            $query->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            });
        }

        foreach ($query->get() as $webhook) {
            $secret = $webhook->secret ?? Str::random(32);
            $signature = hash_hmac('sha256', json_encode($payload), $secret);

            try {
                Http::timeout(5)->withHeaders([
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $event,
                ])->post($webhook->url, $payload);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
