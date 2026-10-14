<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Audit
{
    public static function record(
        Request $request,
        string $action,
        string $description,
        ?Model $subject = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => $request->ip(),
        ]);
    }
}
