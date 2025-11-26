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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Perfecto usar UUID

            $table->text('description');
            // (Recomendación: Crea una tabla 'order_types' para esto)

            $table->json('adjustments')->nullable();
            $table->json('attachments')->nullable();
            $table->dateTime('order_date');
            $table->dateTime('pickup_date')->nullable();
            $table->text('feedback')->nullable();
            $table->integer('rating')->nullable();

            // Relaciones
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('order_type_id')->constrained('order_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
