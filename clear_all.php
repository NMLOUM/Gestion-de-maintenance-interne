<?php

echo "🧹 Nettoyage complet de l'application...\n\n";

// Vider les caches Laravel
system('php artisan cache:clear');
system('php artisan config:clear');
system('php artisan route:clear');
system('php artisan view:clear');
system('php artisan optimize:clear');

echo "\n💾 Vidage des sessions...\n";
// Vider les sessions
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::table('sessions')->truncate();
    echo "✅ Sessions vidées\n";
} catch (Exception $e) {
    echo "⚠️  Impossible de vider les sessions: " . $e->getMessage() . "\n";
}

echo "\n🔧 Reconstruction des assets...\n";
system('npm run build');

echo "\n✅ Nettoyage terminé !\n";
echo "🔄 Vous pouvez maintenant vous reconnecter à l'application.\n";