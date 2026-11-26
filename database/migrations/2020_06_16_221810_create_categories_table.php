<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('名稱');
            $table->text('icon')->nullable()->comment('圖示');
            $table->text('description')->nullable()->comment('描述');
            $table->boolean('is_default')->default(false)->comment('是否為預設分類');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
