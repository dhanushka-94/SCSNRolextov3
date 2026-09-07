<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    /**
     * @return HasMany<RdoDivision, $this>
     */
    public function rdoDivisions(): HasMany
    {
        return $this->hasMany(RdoDivision::class);
    }

    /**
     * @return HasMany<Planter, $this>
     */
    public function planters(): HasMany
    {
        return $this->hasMany(Planter::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * @return list<string>
     */
    public static function activeNames(): array
    {
        return static::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public static function codeFor(?string $name): string
    {
        if (blank($name)) {
            return 'Xx';
        }

        return static::query()->where('name', $name)->value('code') ?? 'Xx';
    }
}
