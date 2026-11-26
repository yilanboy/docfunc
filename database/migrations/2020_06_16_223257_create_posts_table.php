<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->mediumText('body');
            $table->boolean('is_private')->default(false);
            $table->integer('category_id')->unsigned()->index();
            $table->text('excerpt')->nullable();
            $table->string('slug')->nullable();
            $table->string('cover_image_url', 2048)->nullable()->comment('文章預覽圖');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')->index()->constrained()->onDelete('cascade');
            // This line is equivalent to the following two lines in MySQL.
            // $table->bigInteger('user_id')->unsigned()->index();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // In Postgresql, foreign key won't create index by default.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
