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
   Schema::create('endorsements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('policy_id')->constrained('policies')->restrictOnDelete();
    $table->string('endorsement_number', 30)->unique();
    $table->string('endorsement_type');   // 'policy_type_change', 'add_on_added', 'add_on_removed', 'sum_insured_change'
    $table->json('changes');              // {"policy_type": {"from": "third_party", "to": "comprehensive"}}
    $table->decimal('additional_premium', 12, 2)->default(0);
    $table->decimal('refund_premium', 12, 2)->default(0);
    $table->date('effective_date');
    $table->text('reason');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['policy_id', 'effective_date']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
