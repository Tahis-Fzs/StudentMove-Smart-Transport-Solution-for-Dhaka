<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo 'app_key=' . (config('app.key') ? 'set' : 'missing') . PHP_EOL;
    echo 'cache=' . config('cache.default') . PHP_EOL;
    echo 'session=' . config('session.driver') . PHP_EOL;
    echo 'manifest=' . (file_exists(public_path('build/manifest.json')) ? 'yes' : 'no') . PHP_EOL;
    echo 'route_cache=' . (file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 'yes' : 'no') . PHP_EOL;

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::create('/', 'GET', [], [], [], [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'HTTPS' => 'on',
    ]);
    $response = $kernel->handle($request);
    echo 'home_status=' . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() >= 500) {
        $body = $response->getContent();
        if (preg_match('/<!--\s*(.+?)-->/s', $body, $m)) {
            echo 'home_error=' . trim(strip_tags($m[1])) . PHP_EOL;
        } else {
            echo 'home_body=' . substr($body, 0, 400) . PHP_EOL;
        }
    }

    $login = $kernel->handle(Illuminate\Http\Request::create('/login', 'GET', [], [], [], [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'HTTPS' => 'on',
    ]));
    echo 'login_status=' . $login->getStatusCode() . PHP_EOL;
} catch (Throwable $e) {
    http_response_code(500);
    echo 'error=' . $e->getMessage() . PHP_EOL;
}
