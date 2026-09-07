<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditChecklistItem extends Model
{
    public const MODULE_PSM = 'psm';

    public const MODULE_EMM = 'emm';

    public const MODULE_WHSWM = 'whswm';

    public const MODULE_PMM = 'pmm';

    public const MODULE_GRM = 'grm';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'module',
        'code',
        'label',
        'description',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PlanterAuditChecklistAnswer::class);
    }

    /**
     * @return array<string, string>
     */
    public static function modules(): array
    {
        return [
            self::MODULE_PSM => 'Product Safety Module (PSM)',
            self::MODULE_EMM => 'Environmental Management (EMM)',
            self::MODULE_WHSWM => 'Worker Health & Welfare (WHSWM)',
            self::MODULE_PMM => 'Product Marketing (PMM)',
            self::MODULE_GRM => 'General Requirements (GRM)',
        ];
    }

    public function moduleLabel(): string
    {
        return self::modules()[$this->module] ?? strtoupper($this->module);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('module')->orderBy('sort_order')->orderBy('id');
    }
}
