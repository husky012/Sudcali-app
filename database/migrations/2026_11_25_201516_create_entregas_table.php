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
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->unsignedInteger('precision_metros')->nullable();
            $table->timestamp('ubicacion_en')->nullable();
            $table->string('foto')->nullable();
            $table->timestamp('entregado_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
