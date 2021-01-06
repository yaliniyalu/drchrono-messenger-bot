<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Domain\User;
use BotMan\Drivers\Facebook\FacebookDriver;
use Psr\Container\ContainerInterface;

abstract class AbstractConversation extends \BotMan\BotMan\Messages\Conversations\Conversation
{
    private static ContainerInterface $container;
    private static User $user;

    /**
     * @inheritDoc
     */
    abstract public function run();

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public static function setUser(User $user)
    {
        self::$user = $user;
    }

    protected function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    protected function getUser(): User
    {
        return self::$user;
    }

    protected function markSeenAndStartTyping()
    {
        /** @var FacebookDriver $driver */
        $driver = $this->bot->getDriver();
        $driver->markSeen($this->bot->getMessage());
        $this->bot->types();
    }
}
