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
        Schema::table('policies', function (Blueprint $table) {
             $table->string('cancellation_status')->nullable()->after('status');
    $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_status');
    $table->timestamp('cancellation_effective_at')->nullable()->after('cancellation_requested_at');
    $table->timestamp('certificate_surrendered_at')->nullable()->after('cancellation_effective_at');
    $table->decimal('refund_amount', 12, 2)->nullable()->after('certificate_surrendered_at');
    $table->text('cancellation_reason')->nullable()->after('refund_amount');
    $table->foreignId('cancelled_by')->nullable()->after('cancellation_reason')
          ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // Drop the foreign key BEFORE dropping the column
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'cancellation_status',
                'cancellation_requested_at',
                'cancellation_effective_at',
                'certificate_surrendered_at',
                'refund_amount',
                'cancellation_reason',
                'cancelled_by',
            ]);
        });
    }
};
