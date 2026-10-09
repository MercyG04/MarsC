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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number', 30)->unique();           

            // Link to a real client if they exist; null for pure prospects
            $table->foreignId('client_id')->nullable()
            ->constrained('clients')->nullOnDelete();
            $table->string('prospect_name', 150);
            $table->string('prospect_phone', 20)->nullable();
            $table->string('prospect_email', 150)->nullable();
            $table->json('vehicle_details');

            // The quote itself
            $table->string('policy_type');                          
            $table->json('selected_add_on_ids')->nullable();        
            $table->json('premium_breakdown');                      

            $table->decimal('gross_premium', 12, 2);
            $table->string('status');                               // draft | sent | accepted | declined | expired
            $table->timestamp('valid_until');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            // Conversion link
            
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'valid_until']);
            $table->index('quote_number');
        

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
