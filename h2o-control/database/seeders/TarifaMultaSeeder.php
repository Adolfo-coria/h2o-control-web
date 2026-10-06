<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifaMultaSeeder extends Seeder
{
    public function run(): void
    {
        // Desactiva la verificación de llaves foráneas para poder limpiar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('tarifas_multas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Inserta las tarifas con la columna 'tipo'
        DB::table('tarifas_multas')->insert([
            ['tipo' => 'Inasistencia a Desfile Cívico', 'monto' => 50.00, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'Inasistencia a Reunión / Asamblea', 'monto' => 30.00, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'Inasistencia a Trabajo Comunal (Faena/Ayni)', 'monto' => 80.00, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'Otro (Especificar en Detalle)', 'monto' => 0.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}