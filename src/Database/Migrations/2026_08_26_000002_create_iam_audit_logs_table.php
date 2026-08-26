<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('iam_audit_logs')) {
            Schema::create('iam_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('saas_company_id')->nullable()->index();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->string('actor_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->string('subject_type')->nullable();
                $table->string('action')->index();
                $table->json('before_payload')->nullable();
                $table->json('after_payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('correlation_id', 64)->nullable()->index();
                $table->timestamps();
            });
        } else {
            Schema::table('iam_audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('iam_audit_logs', 'saas_company_id')) {
                    $table->unsignedBigInteger('saas_company_id')->nullable()->index();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'actor_id')) {
                    $table->unsignedBigInteger('actor_id')->nullable()->index();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'actor_type')) {
                    $table->string('actor_type')->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'subject_id')) {
                    $table->unsignedBigInteger('subject_id')->nullable()->index();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'subject_type')) {
                    $table->string('subject_type')->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'action')) {
                    $table->string('action')->nullable()->index();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'before_payload')) {
                    $table->json('before_payload')->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'after_payload')) {
                    $table->json('after_payload')->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'user_agent')) {
                    $table->text('user_agent')->nullable();
                }
                if (!Schema::hasColumn('iam_audit_logs', 'correlation_id')) {
                    $table->string('correlation_id', 64)->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iam_audit_logs');
    }
};
