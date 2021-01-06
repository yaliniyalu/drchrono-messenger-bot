<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;

class DrChronoClient
{
    private Client $http;

    public function __construct(string $token)
    {
        $this->http = new Client([
            'base_uri' => 'https://app.drchrono.com/api/',
            'headers' => [ 'Authorization' => 'Bearer ' . $token ]
        ]);
    }

    /**
     * @return Client
     */
    public function getHttp(): Client
    {
        return $this->http;
    }
}
