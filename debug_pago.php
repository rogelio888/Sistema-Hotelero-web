<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $query = \App\Models\Pago::with(['reserva.huesped', 'reserva.habitaciones']);
    $query->activos();
    $pagos = $query->orderBy('id_reserva', 'desc')->orderBy('id', 'desc')->paginate(10);

    file_put_contents('error_log.txt', "Success! Count: " . $pagos->count());

    // Test serialization
    $json = json_encode($pagos);
    if ($json === false) {
        throw new \Exception("JSON encoding failed: " . json_last_error_msg());
    }
    file_put_contents('error_log.txt', "\nSerialization successful.", FILE_APPEND);

} catch (\Throwable $e) {
    $msg = "Error: " . $e->getMessage() . "\n" .
        "File: " . $e->getFile() . ":" . $e->getLine() . "\n" .
        "Stack: " . $e->getTraceAsString();
    file_put_contents('error_log.txt', $msg);
}
