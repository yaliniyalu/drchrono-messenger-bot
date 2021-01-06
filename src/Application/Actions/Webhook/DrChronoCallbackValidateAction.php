<?php

declare(strict_types=1);

namespace App\Application\Actions\Webhook;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;

class DrChronoCallbackValidateAction extends \App\Application\Actions\Action
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $msg = $this->request->getQueryParams()['msg'];
        $sig = hash_hmac('sha256', $msg, $_ENV['DRCHRONO_VERIFY_TOKEN']);
        $this->response->getBody()->write(json_encode(['secret_token' => $sig]));
        return $this->response->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
