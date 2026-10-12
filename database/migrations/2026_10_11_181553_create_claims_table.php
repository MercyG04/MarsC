<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ClaimStatus;
use App\Enums\ClaimType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
             $table->string('claim_number', 30)->unique();   // CLM-2026-00001

            $table->foreignId('policy_id')
                  ->constrained('policies')
                  ->restrictOnDelete();
           
            $table->date('incident_date');
            $table->text('description');
            $table->string('police_abstract_ref', 100)->nullable();
            $table->string('location', 200)->nullable();
            $table->foreignId('driver_id')->nullable()
                  ->constrained('vehicle_drivers')
                  ->nullOnDelete();

            // Financials
            $table->decimal('claimed_amount', 12, 2);
            $table->decimal('assessed_amount', 12, 2)->nullable();
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('deductible_applied', 12, 2)->nullable();
            $table->decimal('salvage_value', 12, 2)->nullable();

            // Classification
            $table->string('claim_type');                    
            $table->string('adjustment_type')->nullable();
            $table->string('status')->default(ClaimStatus::Submitted->value);

            // Rejection
            $table->string('rejection_reason')->nullable();
            $table->text('rejection_notes')->nullable();

            // Assignment
            $table->foreignId('assigned_to')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('assessor_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('reported_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('discharge_voucher_signed_at')->nullable();
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
      
            $table->timestamps();

            $table->softDeletes();

            $table->index(['status', 'reported_at']);
            $table->index('incident_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
