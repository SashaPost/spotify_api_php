<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('playlist_duration', function (Blueprint $table) 
        {
            $table->id();
            $table->unsignedBigInteger('playlist_id');
            // $table->unsignedBigInteger();
            $table->integer('duration_ms');
            $table->timestamps();
            $table->foreign('playlist_id')->references('id')->on('playlists');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 
        Schema::dropIfExists('playlist_duration');
    }
};
