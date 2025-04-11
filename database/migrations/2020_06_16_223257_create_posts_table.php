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
            $table->integer('category_id')->unsigned()->index();
            $table->text('excerpt')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->foreignId('user_id')->index()->constrained()->onDelete('cascade');
            // This line is equivalent to the following two line in MySQL.
            // $table->bigInteger('user_id')->unsigned()->index();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // In Postgresql, foreign key won't create index by default.
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::drop('posts');
    }
};
