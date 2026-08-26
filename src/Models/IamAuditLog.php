<?php

namespace Athka\AuthKit\Models;

use Illuminate\Database\Eloquent\Model;

class IamAuditLog extends Model
{
    protected $table = 'iam_audit_logs';

    protected $fillable = [
        'saas_company_id',
        'actor_id',
        'actor_type',
        'subject_id',
        'subject_type',
        'action',
        'before_payload',
        'after_payload',
        'ip_address',
        'user_agent',
        'correlation_id',
    ];

    protected $casts = [
        'before_payload' => 'array',
        'after_payload'  => 'array',
    ];

    public function actor()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
