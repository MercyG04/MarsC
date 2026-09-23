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
        Schema::create('valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();

            $table->date('valuation_date');

            
            $table->string('valuation_firm', 150);
            $table->string('valuer_name', 150)->nullable();

            
            $table->string('valuation_type')->default('initial');
            $table->decimal('actual_cash_value', 12, 2);
            $table->decimal('forced_sale_value', 12, 2)->nullable();

            
            $table->string('report_path')->nullable();
            $table->text('notes')->nullable();

            
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_id', 'valuation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valuations');
    }
};
