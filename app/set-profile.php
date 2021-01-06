<?php

use Kerox\Messenger\Messenger;

const APP_ROOT = __DIR__ . '/..';

require APP_ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

$messenger = new Messenger(
    $_ENV['FACEBOOK_APP_SECRET'],
    $_ENV['FACEBOOK_VERIFY_TOKEN'],
    $_ENV['FACEBOOK_PAGE_ACCESS_TOKEN']
);

$service = new \App\Service\ProfileService($messenger);
$service->setProfile();
//$service->deleteProfile();
//echo $service->getProfile()->getResponse()->getBody();
