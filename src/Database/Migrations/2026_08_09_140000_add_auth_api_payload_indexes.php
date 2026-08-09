<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_documents')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                if (! $this->hasIndex('employee_documents', 'idx_employee_documents_employee_type')) {
                    $table->index(['employee_id', 'type'], 'idx_employee_documents_employee_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_documents')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                if ($this->hasIndex('employee_documents', 'idx_employee_documents_employee_type')) {
                    $table->dropIndex('idx_employee_documents_employee_type');
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
