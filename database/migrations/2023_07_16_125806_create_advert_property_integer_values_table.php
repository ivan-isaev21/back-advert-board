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
        Schema::create('advert_property_integer_values', function (Blueprint $table) {
            $table->unsignedBigInteger('advert_id')->references('id')->on('adverts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('property_id')->references('id')->on('advert_properties')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('value');
            $table->timestamps();
            $table->primary(['advert_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_property_integer_values');
    }
};
