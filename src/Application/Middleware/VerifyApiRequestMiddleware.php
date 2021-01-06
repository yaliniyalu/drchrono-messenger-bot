<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Base64Url\Base64Url;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class VerifyApiRequestMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeader('Authorization');
        if (empty($token)) {
            return (new ResponseFactory())->createResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        $signed_request = $token[0];
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $sig = Base64Url::decode($encoded_sig);
        $data = json_decode(Base64Url::decode($payload), true);

        $expected_sig = hash_hmac('sha256', $payload, $_ENV['FACEBOOK_APP_SECRET'], $raw = true);

        if ($sig !== $expected_sig) {
            return (new ResponseFactory())->createResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }

        return $handler->handle($request);
    }
}
