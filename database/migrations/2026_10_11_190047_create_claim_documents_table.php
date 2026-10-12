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
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table ->string('type', 50);
            $table->foreignId('claim_id')
                  ->constrained('claims')
                  ->cascadeOnDelete();
            $table->string('original_filename', 255)->nullable();
            $table->string('path', 500);             // S3 key
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            // Lifecycle
            $table->foreignId('uploaded_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('received_at');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('notes')->nullable();            
            $table->timestamps();
            $table->softDeletes();
            $table->index(['claim_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};
