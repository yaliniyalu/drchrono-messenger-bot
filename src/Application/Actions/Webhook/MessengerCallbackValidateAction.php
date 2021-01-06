<?php

declare(strict_types=1);

namespace App\Application\Actions\Webhook;

use App\Application\Actions\Action;
use Fig\Http\Message\StatusCodeInterface;
use Kerox\Messenger\Messenger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class MessengerCallbackValidateAction extends Action
{
    private Messenger $messenger;

    public function __construct(LoggerInterface $logger, Messenger $messenger)
    {
        parent::__construct($logger);

        $this->messenger = $messenger;
    }

    protected function action(): Response
    {
        if (!$this->messenger->webhook()->isValidToken()) {
            return $this->response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        $challenge = $this->messenger->webhook()->challenge();

        $this->response->getBody()->write($challenge);

        return $this->response->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
