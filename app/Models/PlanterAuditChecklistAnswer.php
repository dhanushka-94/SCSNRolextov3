<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanterAuditChecklistAnswer extends Model
{
    public const RESULT_PENDING = 'pending';

    public const RESULT_PASS = 'pass';

    public const RESULT_FAIL = 'fail';

    public const RESULT_NA = 'na';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'planter_audit_id',
        'audit_checklist_item_id',
        'result',
        'comment',
        'answered_by',
        'answered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
        ];
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(PlanterAudit::class, 'planter_audit_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AuditChecklistItem::class, 'audit_checklist_item_id');
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    /**
     * @return array<string, string>
     */
    public static function results(): array
    {
        return [
            self::RESULT_PENDING => 'Pending',
            self::RESULT_PASS => 'Pass',
            self::RESULT_FAIL => 'Fail',
            self::RESULT_NA => 'N/A',
        ];
    }
}
