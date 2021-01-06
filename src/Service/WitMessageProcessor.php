<?php

namespace App\Service;

use Psr\Container\ContainerInterface;

class WitMessageProcessor
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(array $nlp)
    {

    }
}
