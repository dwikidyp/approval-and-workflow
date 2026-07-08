<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE approvals
            MODIFY approved_by BIGINT UNSIGNED NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE approvals
            MODIFY approved_by BIGINT UNSIGNED NOT NULL
        ");
    }
};