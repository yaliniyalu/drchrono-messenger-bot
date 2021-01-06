<?php

declare(strict_types=1);

namespace App\Application\Actions\Medication;

use App\Service\DrChronoPatientHealthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetMedicationAction extends \App\Application\Actions\Action
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
        $medication = $this->drChronoHealthService->getMedication($id);
        return $this->respondWithData($medication);
    }
}
