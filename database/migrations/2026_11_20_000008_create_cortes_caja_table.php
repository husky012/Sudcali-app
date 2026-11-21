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
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('ventas_efectivo', 10, 2)->default(0);
            $table->decimal('ventas_tarjeta', 10, 2)->default(0);
            $table->decimal('monto_final_esperado', 10, 2)->nullable();
            $table->decimal('monto_final_real', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->string('estado')->default('abierto')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};
