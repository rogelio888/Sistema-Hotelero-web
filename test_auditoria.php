<?php

use App\Models\Servicio;
use App\Models\Auditoria;

echo "=== PRUEBA DE AUDITORÍA ===" . PHP_EOL . PHP_EOL;

// Test 1: CREATE
echo "1. Creando servicio..." . PHP_EOL;
$servicio = Servicio::create([
    'nombre' => 'Test Auditoría',
    'descripcion' => 'Prueba de auditoría',
    'tipo' => 'HABITACION',
    'frecuencia' => 'UNICO',
    'precio' => 100,
    'estado' => 'ACTIVO'
]);
echo "   Servicio creado: ID {$servicio->id}" . PHP_EOL;

$auditCreate = Auditoria::where('modelo', 'App\\Models\\Servicio')
    ->where('modelo_id', $servicio->id)
    ->where('accion', 'CREATE')
    ->latest()
    ->first();

if ($auditCreate) {
    echo "   ✓ Auditoría CREATE registrada" . PHP_EOL;
    echo "   - IP: {$auditCreate->ip_address}" . PHP_EOL;
    echo "   - Valores nuevos: " . json_encode($auditCreate->valores_nuevos) . PHP_EOL;
} else {
    echo "   ✗ NO se registró auditoría CREATE" . PHP_EOL;
}

echo PHP_EOL;

// Test 2: UPDATE
echo "2. Actualizando servicio..." . PHP_EOL;
$servicio->update(['precio' => 150]);
echo "   Precio actualizado: 100 -> 150" . PHP_EOL;

$auditUpdate = Auditoria::where('modelo', 'App\\Models\\Servicio')
    ->where('modelo_id', $servicio->id)
    ->where('accion', 'UPDATE')
    ->latest()
    ->first();

if ($auditUpdate) {
    echo "   ✓ Auditoría UPDATE registrada" . PHP_EOL;
    echo "   - Valores antiguos: " . json_encode($auditUpdate->valores_antiguos) . PHP_EOL;
    echo "   - Valores nuevos: " . json_encode($auditUpdate->valores_nuevos) . PHP_EOL;
} else {
    echo "   ✗ NO se registró auditoría UPDATE" . PHP_EOL;
}

echo PHP_EOL;

// Test 3: DELETE
echo "3. Eliminando servicio..." . PHP_EOL;
$servicioId = $servicio->id;
$servicio->delete();
echo "   Servicio eliminado" . PHP_EOL;

$auditDelete = Auditoria::where('modelo', 'App\\Models\\Servicio')
    ->where('modelo_id', $servicioId)
    ->where('accion', 'DELETE')
    ->latest()
    ->first();

if ($auditDelete) {
    echo "   ✓ Auditoría DELETE registrada" . PHP_EOL;
    echo "   - Valores antiguos: " . json_encode($auditDelete->valores_antiguos) . PHP_EOL;
} else {
    echo "   ✗ NO se registró auditoría DELETE" . PHP_EOL;
}

echo PHP_EOL;

// Resumen
echo "=== RESUMEN ===" . PHP_EOL;
$totalAudits = Auditoria::where('modelo', 'App\\Models\\Servicio')
    ->where('modelo_id', $servicioId)
    ->count();
echo "Total de auditorías registradas: {$totalAudits}/3" . PHP_EOL;

if ($totalAudits === 3) {
    echo "✓ TODAS LAS OPERACIONES FUERON AUDITADAS CORRECTAMENTE" . PHP_EOL;
} else {
    echo "✗ FALTAN AUDITORÍAS" . PHP_EOL;
}
