<?php

declare(strict_types=1);

namespace App\Conversation\Middleware;

use App\Conversation\AbstractConversation;
use App\Domain\User;
use App\Service\UserService;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use Kerox\Messenger\Messenger;

class OnMessageReceived implements Received
{
    private UserService $userService;
    private Messenger $messenger;

    public function __construct(UserService $userService, Messenger $messenger)
    {
        $this->userService = $userService;
        $this->messenger = $messenger;
    }

    public function received(IncomingMessage $message, $next, BotMan $bot)
    {
        $sender = $message->getSender();
        try {
            $user = $this->userService->get($sender);
        } catch (\Exception $e) {
            $u = $this->messenger->user()->profile($sender);
            $user = new User($sender, $u->getFirstName() . ' ' . $u->getLastName());
            $this->userService->save($user);
        }

        $message->addExtras('user', $user);
        AbstractConversation::setUser($user);
        return $next($message);
    }
}
