<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // No-op: this migration is intentionally left empty because
        // the foreign key on payments.processed_by is handled by a
        // later migration (2026_02_25_000005_*).
    }

    public function down()
    {
        // No-op: nothing to rollback because this migration does not
        // apply any schema changes.
    }
};
