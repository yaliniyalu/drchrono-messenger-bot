<?php

declare(strict_types=1);

namespace App\Conversation\Middleware;

use App\Domain\User;
use BotMan\BotMan\Interfaces\Middleware\Matching;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class IsLoggedInMiddleware implements Matching
{
    public function matching(IncomingMessage $message, $pattern, $regexMatched)
    {
        /** @var User $user */
        $user = $message->getExtras('user');
        return $regexMatched && $user->getPatientId();
    }
}
