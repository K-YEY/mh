<?php

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLoggerService
{
    /**
     * Log a mutation event on an ERP model entity.
     */
    public static function log(
        Model $model,
        string $event,
        ?string $description = null,
        ?array $customProperties = null
    ): void {
        $original = $model->getOriginal();
        $changes = $model->getChanges();

        $properties = $customProperties ?? [
            'original' => $original,
            'changes' => $changes,
        ];

        DB::table('activity_log')->insert([
            'log_name' => 'erp_audit',
            'description' => $description ?? 'Model '.class_basename($model)." {$event}ed (ID: {$model->getKey()})",
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'event' => $event,
            'causer_type' => auth()->user() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'properties' => json_encode($properties),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
