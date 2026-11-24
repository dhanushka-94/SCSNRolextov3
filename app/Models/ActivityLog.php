<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/**
 * Append-only audit trail of security-relevant actions: sign-ins, account changes,
 * registry decisions, certificates and maintenance mode. Never store passwords or tokens
 * in $properties.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event',
        'causer_type',
        'causer_id',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Records one event. The causer defaults to whoever is signed in on this request
     * (web admin, API staff token or planter token).
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $event, ?Model $subject = null, array $properties = [], ?Model $causer = null): self
    {
        $causer ??= Auth::guard('web')->user()
            ?? Auth::guard('sanctum')->user()
            ?? Auth::guard('planter')->user();

        $request = request();

        return static::query()->create([
            'event' => $event,
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255) ?: null,
        ]);
    }
}
