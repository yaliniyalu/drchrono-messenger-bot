<?php

declare(strict_types=1);

namespace App\Application\Actions\LabResult;

use App\Service\DrChronoPatientHealthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetLabResult extends \App\Application\Actions\Action
{
    private DrChronoPatientHealthService $drChronoHealthService;

    public function __construct(LoggerInterface $logger, DrChronoPatientHealthService $drChronoHealthService)
    {
        parent::__construct($logger);

        $this->drChronoHealthService = $drChronoHealthService;
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $id = (int) $this->args['id'];
        $labResult = $this->drChronoHealthService->getLabResult($id);
        return $this->respondWithData($labResult);
    }
}
