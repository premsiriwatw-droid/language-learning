<?php

use App\Models\User;
use App\Services\Shop\ShopService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Only launched by the concurrency suite against its temporary SQLite database.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! $app->environment('testing') || config('database.default') !== 'sqlite'
    || ! str_contains(basename(config('database.connections.sqlite.database')), 'shop-race-')) {
    fwrite(STDERR, 'Refusing non-test database: '.app()->environment().' / '.config('database.default').' / '.basename(config('database.connections.sqlite.database')).PHP_EOL);
    exit(3);
}

DB::statement('PRAGMA busy_timeout = 5000');
file_put_contents($argv[3].'.'.$argv[4].'.ready', 'ready');
$deadline = microtime(true) + 15;
while (! is_file($argv[3])) {
    if (microtime(true) > $deadline) {
        exit(4);
    }
    usleep(10000);
    clearstatcache();
}

try {
    app(ShopService::class)->purchase(User::findOrFail((int) $argv[1]), (int) $argv[2]);
    fwrite(STDOUT, 'purchased');
    exit(0);
} catch (ValidationException) {
    fwrite(STDOUT, 'insufficient');
    exit(2);
}
