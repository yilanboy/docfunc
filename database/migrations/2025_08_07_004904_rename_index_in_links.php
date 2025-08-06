<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->renameIndex('links_link_index', 'links_url_index');
        });
    }

    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->renameIndex('links_url_index', 'links_link_index');
        });
    }
};
