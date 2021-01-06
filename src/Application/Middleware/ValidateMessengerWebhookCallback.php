<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Kerox\Messenger\Messenger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class ValidateMessengerWebhookCallback implements Middleware
{
    private Messenger $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (!$this->messenger->webhook()->isValidCallback()) {
            return (new ResponseFactory())->createResponse(StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        return $handler->handle($request);
    }
}
