<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('approval_policy_steps')) {
            Schema::table('approval_policy_steps', function (Blueprint $table) {
                if (! $this->hasIndex('approval_policy_steps', 'idx_approval_steps_approver_id')) {
                    $table->index('approver_id', 'idx_approval_steps_approver_id');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! $this->hasIndex('users', 'idx_users_device_company_active')) {
                    $table->index(['device_id', 'saas_company_id', 'is_active'], 'idx_users_device_company_active');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->hasIndex('users', 'idx_users_device_company_active')) {
                    $table->dropIndex('idx_users_device_company_active');
                }
            });
        }

        if (Schema::hasTable('approval_policy_steps')) {
            Schema::table('approval_policy_steps', function (Blueprint $table) {
                if ($this->hasIndex('approval_policy_steps', 'idx_approval_steps_approver_id')) {
                    $table->dropIndex('idx_approval_steps_approver_id');
                }
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'mysql') {
            $database = $connection->getDatabaseName();
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );

            return (int) $result[0]->count > 0;
        }

        if ($connection->getDriverName() === 'sqlite') {
            return count($connection->select(
                "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?",
                [$table, $indexName]
            )) > 0;
        }

        return false;
    }
};
