<?php

namespace Athka\AuthKit\Support;

use Athka\AuthKit\Models\IamAuditLog;
use App\Support\LogSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Log an Identity and Access Management (IAM) or Authentication event.
     */
    public static function logIam(
        string $action,
        ?Model $subject = null,
        array $before = [],
        array $after = [],
        ?int $companyId = null
    ): ?IamAuditLog {
        try {
            $user = Auth::user();
            $actorId = $user ? $user->getAuthIdentifier() : null;
            $actorType = $user ? get_class($user) : null;

            $tenantId = $companyId ?: ($user->saas_company_id ?? $user->company_id ?? null);

            $subjectId = $subject ? $subject->getKey() : null;
            $subjectType = $subject ? get_class($subject) : null;

            $request = app()->bound('request') ? request() : null;
            $ip = ($request && method_exists($request, 'ip')) ? $request->ip() : null;
            $userAgent = ($request && method_exists($request, 'userAgent')) ? substr((string) $request->userAgent(), 0, 500) : null;
            $correlationId = ($request && method_exists($request, 'header')) ? $request->header('X-Correlation-ID') : null;

            $beforeClean = (!empty($before) && class_exists('\App\Support\LogSanitizer')) ? LogSanitizer::clean($before) : $before;
            $afterClean  = (!empty($after) && class_exists('\App\Support\LogSanitizer')) ? LogSanitizer::clean($after) : $after;

            return IamAuditLog::create([
                'saas_company_id' => $tenantId,
                'actor_id'        => $actorId,
                'actor_type'      => $actorType,
                'subject_id'      => $subjectId,
                'subject_type'    => $subjectType,
                'action'          => $action,
                'before_payload'  => $beforeClean ?: null,
                'after_payload'   => $afterClean ?: null,
                'ip_address'      => $ip,
                'user_agent'      => $userAgent,
                'correlation_id'  => $correlationId,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AuditLogger Failure: " . $e->getMessage());
            return null;
        }
    }
}
