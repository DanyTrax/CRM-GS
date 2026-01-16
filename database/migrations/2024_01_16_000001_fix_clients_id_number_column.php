<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la columna existe y cambiar su tipo si es necesario
        $columnType = DB::select("SHOW COLUMNS FROM clients WHERE Field = 'id_number'");
        
        if (!empty($columnType)) {
            $currentType = $columnType[0]->Type;
            
            // Si es INT o similar, cambiarlo a VARCHAR
            if (strpos(strtolower($currentType), 'int') !== false || 
                strpos(strtolower($currentType), 'bigint') !== false) {
                
                Schema::table('clients', function (Blueprint $table) {
                    $table->string('id_number', 50)->change();
                });
            } elseif (strpos(strtolower($currentType), 'varchar') !== false) {
                // Si ya es VARCHAR pero es muy corto, aumentarlo
                $length = (int) preg_replace('/[^0-9]/', '', $currentType);
                if ($length < 50) {
                    Schema::table('clients', function (Blueprint $table) {
                        $table->string('id_number', 50)->change();
                    });
                }
            }
        }
    }

    public function down(): void
    {
        // No hacer nada en el rollback para evitar pérdida de datos
    }
};
