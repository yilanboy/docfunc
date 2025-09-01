<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('passkeys', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);

            // Rename user_id to owner_id
            $table->renameColumn('user_id', 'owner_id');

            // Add an owner_type column
            $table->string('owner_type')->nullable()->after('owner_id');
        });

        // Set all existing records to a User type
        DB::table('passkeys')->update([
            'owner_type' => 'App\Models\User',
        ]);

        // Make owner_type non-nullable
        Schema::table('passkeys', function (Blueprint $table) {
            $table->string('owner_type')
                ->nullable(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passkeys', function (Blueprint $table) {
            // Drop the owner_type column
            $table->dropColumn('owner_type');

            // Rename owner_id back to user_id
            $table->renameColumn('owner_id', 'user_id');

            // Re-add the foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
