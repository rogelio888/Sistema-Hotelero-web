<?php
$output = [];
$returnVar = 0;
exec('php artisan migrate 2>&1', $output, $returnVar);
file_put_contents('migration_debug.txt', implode("\n", $output));
