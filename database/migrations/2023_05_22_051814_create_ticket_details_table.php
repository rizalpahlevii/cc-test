<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_details', function (Blueprint $table) {
            $table->id();
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('ticket_code')->nullable();
            $table->foreignId('ticket_id')->constrained();
            $table->foreignId('bus_class_id')->constrained();
            $table->tinyInteger('seat_number')->nullable();
            $table->double('price');
            $table->double('total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_details');
    }
};
