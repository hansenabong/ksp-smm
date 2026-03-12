<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Record an audit event.
     *
     * @param string      $action       e.g. 'void', 'approve', 'reject', 'login', 'logout'
     * @param string|null $subjectType  e.g. 'CashTransaction', 'LoanApplication'
     * @param int|null    $subjectId    PK of the subject record
     * @param string|null $description  Human-readable summary
     * @param array       $metadata     Additional context (reason, old/new values, etc.)
     */
    public function record(
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $description = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'ip_address'   => request()->ip(),
            'metadata'     => $metadata ?: null,
        ]);
    }
}
