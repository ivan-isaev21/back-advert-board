<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advert_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advert_id');
            $table->string('file_hash');
            $table->string('file_path');
            $table->string('file_original_name');
            $table->timestamps();

            $table->foreign('advert_id')->references('id')->on('adverts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['advert_id', 'file_path']);
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_images');
    }
};
