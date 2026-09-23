<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\VehicleUse;
use App\Enums\VehicleStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();
            $table->string('registration_number', 20)->unique();   
            $table->string('chassis_number', 50)->unique();        
            $table->string('logbook_number', 50)->unique();
            $table->string('make', 80);                            
            $table->string('model', 80);                           
            $table->decimal('initial_estimated_value', 12, 2);     
            $table->string('vehicle_use')->default(VehicleUse::Personal->value);
            $table->string('status')->default(VehicleStatus::PendingValuation->value);
                  
                  
            $table->boolean('ntsa_verified')->default(false);
            $table->timestamp('ntsa_verified_at')->nullable();

           
            $table->softDeletes();

            $table->index('vehicle_use');

            $table->unsignedSmallInteger('year_of_manufacture');      
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
