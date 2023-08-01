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
        Schema::create('advert_property_boolean_values', function (Blueprint $table) {
            $table->id();
            $table->string('advert_id');
            $table->unsignedBigInteger('property_id');
            $table->boolean('value')->default(false);
            $table->timestamps();
            $table->unique(['advert_id', 'property_id']);

            $table->foreign('advert_id')->references('id')->on('adverts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('property_id')->references('id')->on('advert_properties')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_property_boolean_values');
    }
};
