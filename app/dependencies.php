<?php

declare(strict_types=1);

use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\Psr6Cache;
use BotMan\BotMan\Drivers\DriverManager;
use DI\ContainerBuilder;
use Kerox\Messenger\Messenger;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Phpfastcache\CacheManager;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        \Kerox\Messenger\Messenger::class => function (ContainerInterface $c) {
            return new Messenger(
                $_ENV['FACEBOOK_APP_SECRET'],
                $_ENV['FACEBOOK_VERIFY_TOKEN'],
                $_ENV['FACEBOOK_PAGE_ACCESS_TOKEN']
            );
        },

        \MysqliDb::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['mysql'];
            return new \MysqliDb($settings);
        },

        \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['cache'];
            $config = new \Phpfastcache\Config\ConfigurationOption($settings['config']);
            return \Phpfastcache\CacheManager::getInstance($settings['driver'], $config);
        },

        \BotMan\BotMan\BotMan::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['botman'];

            DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);
            $cache = new Psr6Cache($c->get(\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface::class));

            return BotManFactory::create($settings, $cache);
        },

        \App\Service\DrChronoClient::class => function (ContainerInterface $c) {

            /** @var \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface $cache */
            $cache = $c->get(\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface::class);

            $item = $cache->getItem('drchrono_access_token');
            if ($item->isHit()) {
                return new \App\Service\DrChronoClient($item->get());
            }

            $provider = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => $_ENV['DRCHRONO_CLIENT_ID'],
                'clientSecret'            => $_ENV['DRCHRONO_CLIENT_SECRET'],
                'redirectUri'             => $_ENV['DRCHRONO_REDIRECT_URI'],
                'urlAuthorize'            => 'https://drchrono.com/o/authorize/',
                'urlAccessToken'          => 'https://drchrono.com/o/token/',
                'urlResourceOwnerDetails' => 'https://drchrono.com/o/resource/'
            ]);

            $newToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $_ENV['DRCHRONO_REFRESH_TOKEN']
            ]);

            $item->expiresAfter($newToken->getExpires());
            $item->set($newToken->getToken());

            $cache->setItem($item);

            return new \App\Service\DrChronoClient($newToken->getToken());
        }
    ]);
};
