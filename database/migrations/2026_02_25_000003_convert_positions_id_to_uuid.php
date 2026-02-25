<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        // 1. Drop foreign key constraints in referencing tables (employees)
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
        });

        // 2. Add a temporary uuid column to positions
        Schema::table('positions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // 3. Populate uuid column for all existing rows
        $positions = DB::table('positions')->get();
        foreach ($positions as $position) {
            $uuid = (string) Str::uuid();
            DB::table('positions')->where('id', $position->id)->update(['uuid' => $uuid]);

            // 4. Update referencing tables with new uuid
            DB::table('employees')->where('position_id', $position->id)->update(['position_id' => $uuid]);
        }

        // 5. Change position_id column type in referencing tables
        Schema::table('employees', function (Blueprint $table) {
            $table->uuid('position_id')->change();
        });

        // 6. Remove auto-increment from id and drop primary key
        DB::statement('ALTER TABLE positions MODIFY id BIGINT NOT NULL');
        DB::statement('ALTER TABLE positions DROP PRIMARY KEY');

        // 7. Make uuid the new primary key
        DB::statement('ALTER TABLE positions ADD PRIMARY KEY (uuid)');

        // 8. Drop old id column and rename uuid to id
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->renameColumn('uuid', 'id');
        });

        // 9. Recreate foreign key constraints
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
        });
    }

    public function down()
    {
        // This migration is intentionally irreversible.
        // The original integer IDs were replaced by UUIDs and then dropped,
        // so there is no safe way to restore the previous state.
        throw new \RuntimeException('Migration 2026_02_25_000003_convert_positions_id_to_uuid cannot be rolled back safely.');
    }
};
