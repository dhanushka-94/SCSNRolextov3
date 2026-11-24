<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    public const MAINTENANCE_MODE = 'maintenance_mode';

    public const MAINTENANCE_MESSAGE = 'maintenance_message';

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value', 'updated_by'];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function tableReady(): bool
    {
        static $ready = null;

        return $ready ??= Schema::hasTable('system_settings');
    }

    public static function read(string $key, ?string $default = null): ?string
    {
        if (! self::tableReady()) {
            return $default;
        }

        $value = self::query()->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public static function write(string $key, ?string $value, ?int $userId = null): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $userId],
        );
    }

    public static function maintenanceEnabled(): bool
    {
        return self::read(self::MAINTENANCE_MODE, '0') === '1';
    }

    public static function maintenanceMessage(): ?string
    {
        $message = self::read(self::MAINTENANCE_MESSAGE);

        return filled($message) ? $message : null;
    }
}
