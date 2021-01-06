<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Domain\User;
use Exception;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Message;

class MessageService
{
    protected Messenger $messenger;
    protected User $user;

    public function __construct(Messenger $messenger, User $user)
    {
        $this->messenger = $messenger;
        $this->user = $user;
    }

    /**
     * @param Message $message
     * @throws Exception
     */
    public function send(Message $message): void
    {
        $this->messenger->send()->message($this->user->getId(), $message);
    }
}
