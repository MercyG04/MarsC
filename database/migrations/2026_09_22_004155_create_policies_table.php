<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PaymentFrequency;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_number', 50)->unique();
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                  ->constrained('vehicles')
                  ->restrictOnDelete();
            $table->string('policy_type')->default(PolicyType::Comprehensive->value);
            $table->string('status')->default(PolicyStatus::Pending->value);
            $table->date('issued_at')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('basic_premium', 12, 2)->default(0);
            $table->decimal('training_levy', 12, 2)->default(0);
            $table->decimal('phcf', 12, 2)->default(0);
            $table->decimal('stamp_duty', 12, 2)->default(40.00);
                       
            $table->decimal('gross_premium', 12, 2)->default(0);
            $table->decimal('sum_insured', 12, 2)->default(0);
            $table->decimal('deductible_amount', 12, 2)->default(0);
            $table->string('payment_frequency')->default(PaymentFrequency::Annual->value);
            $table->decimal('premium_balance', 12, 2)->default(0);
            $table->timestamp('certificate_issued_at')->nullable();
            $table->softDeletes();
            $table->index(['status', 'end_date']);              // renewal reminders
            $table->index(['payment_frequency', 'premium_balance']); // overdue monthly payers
            $table->index('policy_type');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
