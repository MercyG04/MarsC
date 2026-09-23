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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table-> string('first_name');
            $table-> string('last_name');
            $table-> string('other_names')->nullable();
            $table-> string('national_id')->unique();
            $table->string('kra_pin');
            $table-> string('phone_number');
            $table-> string('email')->unique();
            $table-> date('date_of_birth');
            $table-> string('occupation')->nullable();
            $table-> string('county');
            $table-> string('physical_location');
            $table->enum('gender', ['male', 'female']);
            $table->unsignedTinyInteger('driving_experience');
            $table->boolean('consent_given')->default(false); 
           $table->timestamp('consent_given_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
