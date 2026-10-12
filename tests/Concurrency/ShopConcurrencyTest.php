<?php

namespace Tests\Concurrency;

use App\Models\Purchase;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Services\Progress\StarRewardService;
use App\Services\Shop\StarWallet;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ShopConcurrencyTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = sys_get_temp_dir().DIRECTORY_SEPARATOR.'shop-race-'.bin2hex(random_bytes(12)).'.sqlite';
        file_put_contents($this->database, '');
        config()->set('database.connections.sqlite.database', $this->database);
        DB::purge('sqlite');
        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite');
        if (isset($this->database)) {
            foreach ([$this->database, $this->database.'-wal', $this->database.'-shm', $this->database.'.go',
                $this->database.'.go.0.ready', $this->database.'.go.1.ready'] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
        parent::tearDown();
    }

    private function race(bool $sameItem): array
    {
        $user = User::factory()->create();
        app(StarRewardService::class)->add($user, 'race-fixture', 100);
        $items = collect(['gold', 'ocean'])->map(fn ($style) => ShopItem::create([
            'sku' => 'frame-'.$style, 'name' => $style, 'type' => 'frame', 'style_key' => $style, 'price_stars' => 80,
        ]));
        $gate = $this->database.'.go';
        $processes = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = new Process([
                    PHP_BINARY, base_path('tests/Support/shop-purchase-worker.php'),
                    (string) $user->id, (string) $items[$sameItem ? 0 : $i]->id, $gate, (string) $i,
                ], base_path(), [
                    'APP_ENV' => 'testing', 'APP_KEY' => config('app.key'),
                    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $this->database, 'DB_URL' => '',
                    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
                ]);
                $process->setTimeout(25)->start();
                $processes[] = $process;
            }
            $deadline = microtime(true) + 15;
            while (! is_file($gate.'.0.ready') || ! is_file($gate.'.1.ready')) {
                foreach ($processes as $process) {
                    $this->assertTrue($process->isRunning(), $process->getErrorOutput().$process->getOutput());
                }
                $this->assertLessThan($deadline, microtime(true), 'Purchase workers did not become ready');
                usleep(10000);
                clearstatcache();
            }
            file_put_contents($gate, 'go');
            $codes = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertContains($process->getExitCode(), [0, 2], $process->getErrorOutput().$process->getOutput());
                $this->assertContains($process->getOutput(), ['purchased', 'insufficient'], $process->getErrorOutput().$process->getOutput());
                $codes[] = $process->getOutput() === 'purchased' ? 0 : 2;
            }
            $this->assertSame(1, Purchase::count());
            $this->assertSame(1, UserItem::count());
            $this->assertSame(20, app(StarWallet::class)->summary($user)['balance']);
            sort($codes);

            return $codes;
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
        }
    }

    public function test_two_simultaneous_different_purchases_cannot_overdraw(): void
    {
        $this->assertSame([0, 2], $this->race(false));
    }

    public function test_two_simultaneous_same_item_purchases_charge_once(): void
    {
        $this->assertSame([0, 0], $this->race(true));
    }
}
