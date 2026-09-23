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
        Schema::create('policy_add_on', function (Blueprint $table) {
        $table->id();
        $table->foreignId('policy_id')
          ->constrained('policies')
          ->cascadeOnDelete();       
        $table->foreignId('add_on_id')
          ->constrained('add_ons')
          ->restrictOnDelete();      
        $table->decimal('charged_amount', 12, 2);   
         $table->timestamps();

        $table->unique(['policy_id', 'add_on_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policy_add_on');
    }
};
