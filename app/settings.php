<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
            'mysql' => [
                'host' => $_ENV['DATABASE_HOST'],
                'username' => $_ENV['DATABASE_USER'],
                'password' => $_ENV['DATABASE_PASSWORD'],
                'db' => $_ENV['DATABASE_NAME'],
                'charset' => 'utf8mb4'
            ],
            'cache' => [
                'driver' => 'files',
                'config' => [
                    'path' => APP_ROOT . '/var/cache/bot'
                ]
            ],
            'botman' => [
                'facebook' => [
                    'token' => $_ENV['FACEBOOK_PAGE_ACCESS_TOKEN'],
                    'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
                    'verification' => $_ENV['FACEBOOK_VERIFY_TOKEN'],
                ]
            ]
        ],
    ]);
};
