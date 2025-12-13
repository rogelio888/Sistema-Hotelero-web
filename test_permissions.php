<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Empleado;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;

try {
    // 1. Buscar o crear un usuario Recepcionista
    $rolRecepcionista = Rol::where('nombre', 'Recepcionista')->first();
    if (!$rolRecepcionista) {
        die("Error: Rol Recepcionista no encontrado.\n");
    }

    $user = Empleado::where('id_rol', $rolRecepcionista->id)->first();
    if (!$user) {
        // Crear uno temporal si no existe
        $user = Empleado::create([
            'nombre' => 'Test Recepcionista',
            'apellido' => 'Test',
            'usuario' => 'test_recepcionista',
            'password' => 'password', // El mutator lo hasheará
            'id_rol' => $rolRecepcionista->id,
            'estado' => 'ACTIVO'
        ]);
        echo "Usuario temporal creado.\n";
    }

    echo "Probando con usuario: " . $user->nombre . " (Rol: " . $user->rol->nombre . ")\n";
    Auth::login($user);

    // 2. Probar Controller directamente
    $controller = new \App\Http\Controllers\Api\TipoHabitacionController();

    // Test Store
    echo "\n--- Probando Store (Crear) ---\n";
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->merge([
        'nombre' => 'Habitación Test',
        'capacidad' => 2,
        'precio_base' => 100,
        'estado' => 'ACTIVO'
    ]);

    $response = $controller->store($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";

    if ($response->getStatusCode() === 403) {
        echo "✅ Store bloqueado correctamente.\n";
    } else {
        echo "❌ Store NO bloqueado (Status: " . $response->getStatusCode() . ").\n";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
