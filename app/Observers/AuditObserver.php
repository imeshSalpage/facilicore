<?php

namespace App\Observers;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditObserver — Catches Eloquent model lifecycle changes and writes clean JSON audit logs automatically.
 */
class AuditObserver
{
    public function __construct(private readonly AuditLogService $auditService)
    {
    }

    public function created(Model $model): void
    {
        $name = strtolower(class_basename($model));
        
        // Custom booking fields for clean visibility
        $payload = $model->toArray();
        unset($payload['created_at'], $payload['updated_at']);

        $this->auditService->log(
            "{$name}.created",
            get_class($model),
            $model->id,
            $payload
        );
    }

    public function updated(Model $model): void
    {
        $name = strtolower(class_basename($model));
        
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (!empty($changes)) {
            $this->auditService->log(
                "{$name}.updated",
                get_class($model),
                $model->id,
                [
                    'changes'  => $changes,
                    'original' => array_intersect_key($model->getRawOriginal(), $changes)
                ]
            );
        }
    }

    public function deleted(Model $model): void
    {
        $name = strtolower(class_basename($model));
        
        $this->auditService->log(
            "{$name}.deleted",
            get_class($model),
            $model->id,
            [
                'name' => $model->name ?? ($model->id ?? null)
            ]
        );
    }
}
