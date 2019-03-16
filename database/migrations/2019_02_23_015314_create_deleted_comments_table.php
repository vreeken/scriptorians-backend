<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeletedCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deleted_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('original_comment_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->tinyInteger('volume_id')->unsigned();
            $table->tinyInteger('book_id')->unsigned();
            $table->tinyInteger('chapter_id')->unsigned();
            $table->tinyInteger('verse_id')->unsigned();
            $table->boolean('public')->default(0);
            $table->string('comment', 4096);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('lineage', 1000)->default('/');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deleted_comments');
    }
}
