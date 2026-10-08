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
        Schema::create('vehicle_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('national_id', 20);
            $table->string('dl_number', 30);
            $table->string('dl_class', 10);   

            $table->date('date_of_birth');
            $table->unsignedTinyInteger('driving_experience');

            $table->boolean('is_primary')->default(false);      
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'is_primary']);
            $table->unique(['vehicle_id', 'national_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_drivers');
    }
};
