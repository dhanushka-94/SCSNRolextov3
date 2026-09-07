<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanterAudit extends Model
{
    public const ROUND_FIRST = 'first';

    public const ROUND_FINAL = 'final';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_PASSED = 'passed';

    public const STATUS_CONDITIONAL = 'conditional';

    public const STATUS_FAILED = 'failed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'planter_id',
        'round',
        'attempt_number',
        'is_current',
        'status',
        'assigned_to',
        'sent_by',
        'completed_by',
        'sent_at',
        'started_at',
        'completed_at',
        'outcome_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'is_current' => 'boolean',
            'sent_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function planter(): BelongsTo
    {
        return $this->belongsTo(Planter::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PlanterAuditChecklistAnswer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public function roundAttempts()
    {
        return self::query()
            ->where('planter_id', $this->planter_id)
            ->where('round', $this->round)
            ->orderByDesc('attempt_number')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public static function rounds(): array
    {
        return [
            self::ROUND_FIRST => 'First Audit',
            self::ROUND_FINAL => 'Final Audit',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_IN_PROGRESS => 'In progress',
            self::STATUS_IN_REVIEW => 'In review',
            self::STATUS_PASSED => 'Passed',
            self::STATUS_CONDITIONAL => 'Conditional',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function roundLabel(): string
    {
        return self::rounds()[$this->round] ?? $this->round;
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_QUEUED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_IN_REVIEW,
        ], true);
    }

    public function isComplete(): bool
    {
        return in_array($this->status, [
            self::STATUS_PASSED,
            self::STATUS_CONDITIONAL,
            self::STATUS_FAILED,
        ], true);
    }

    public function canProceedToFinal(): bool
    {
        return $this->round === self::ROUND_FIRST
            && in_array($this->status, [self::STATUS_PASSED, self::STATUS_CONDITIONAL], true);
    }

    public function checklistProgress(): array
    {
        $total = $this->answers->count();
        $answered = $this->answers->where('result', '!=', PlanterAuditChecklistAnswer::RESULT_PENDING)->count();

        return [
            'total' => $total,
            'answered' => $answered,
            'percent' => $total > 0 ? (int) round(($answered / $total) * 100) : 0,
        ];
    }
}
