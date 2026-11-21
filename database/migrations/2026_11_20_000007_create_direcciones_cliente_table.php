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
        Schema::create('direcciones_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('calle_numero');
            $table->string('colonia');
            $table->string('codigo_postal', 5);
            $table->string('ciudad')->default('La Paz');
            $table->string('estado')->default('Baja California Sur');
            $table->string('referencias')->nullable();
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones_cliente');
    }
};
