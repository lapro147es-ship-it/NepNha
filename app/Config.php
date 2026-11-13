<?php
declare(strict_types=1);

function appConfiguration(string $root): array
{
    $path = $root . '/config.php';
    $settings = require is_file($path) ? $path : $root . '/config.example.php';

    if (getenv('APP_DB_DRIVER') !== false) {
        $settings['driver'] = getenv('APP_DB_DRIVER');
    }

    $envMap = [
        'APP_MYSQL_HOST' => 'mysql_host',
        'APP_MYSQL_PORT' => 'mysql_port',
        'APP_MYSQL_DATABASE' => 'mysql_database',
        'APP_MYSQL_USER' => 'mysql_user',
        'APP_MYSQL_PASSWORD' => 'mysql_password',
    ];

    foreach ($envMap as $environment => $key) {
        if (getenv($environment) !== false) {
            $settings[$key] = getenv($environment);
        }
    }

    if (getenv('APP_DB')) {
        $settings['driver'] = 'sqlite';
        $settings['sqlite_path'] = getenv('APP_DB');
    }

    return $settings;
}
