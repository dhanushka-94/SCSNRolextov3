<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Records successfully applied offline-sync operations so a retried client_id
 * returns the original result instead of applying the change twice.
 */
class ApiSyncOperation extends Model
{
    public const ACTOR_STAFF = 'staff';

    public const ACTOR_PLANTER = 'planter';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_type',
        'actor_id',
        'client_id',
        'type',
        'response',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }
}
