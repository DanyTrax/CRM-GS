#!/bin/bash

echo "🔍 Verificando columnas de la tabla clients..."

php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

echo 'Columnas en la tabla clients:' . PHP_EOL;
\$columns = DB::select('SHOW COLUMNS FROM clients');
foreach (\$columns as \$col) {
    echo '  ✓ ' . \$col->Field . ' (' . \$col->Type . ')' . PHP_EOL;
}

echo PHP_EOL . 'Verificando columnas requeridas:' . PHP_EOL;
\$required = ['company_name', 'email_login', 'email_billing', 'tax_id'];
\$existing = array_map(function(\$col) { return \$col->Field; }, \$columns);
\$existing = array_flip(\$existing);

foreach (\$required as \$col) {
    if (isset(\$existing[\$col])) {
        echo '  ✅ ' . \$col . ' - EXISTE' . PHP_EOL;
    } else {
        echo '  ❌ ' . \$col . ' - NO EXISTE' . PHP_EOL;
    }
}
"
