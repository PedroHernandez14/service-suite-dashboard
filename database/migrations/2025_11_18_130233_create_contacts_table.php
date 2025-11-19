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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Esto crea 'contactable_id' y 'contactable_type'
            // Permite que el contacto pertenezca a Person o Company
            $table->morphs('contactable');

            // Tipo de contacto (Email, Teléfono, WhatsApp, Web)
            $table->string('type');

            // El valor (el correo o el número)
            $table->string('value');

            // Etiqueta opcional (Ej: "Casa", "Trabajo", "Facturación")
            $table->string('label')->nullable();

            // País (Solo si es teléfono)
            $table->string('prefix', 5)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
