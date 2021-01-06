<?php

declare(strict_types=1);

namespace App\Application\Actions\Appointment;

use App\Service\DrChronoAppointmentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetFreeSlotsAction extends \App\Application\Actions\Action
{
    private DrChronoAppointmentService $drChronoAppointmentService;

    public function __construct(LoggerInterface $logger, DrChronoAppointmentService $drChronoAppointmentService)
    {
        parent::__construct($logger);

        $this->drChronoAppointmentService = $drChronoAppointmentService;
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $body = $this->request->getQueryParams();

        $slots = $this->drChronoAppointmentService->getFreeSlots(
            (int) $body['office'],
            $body['date'],
            (int) $body['duration']
        );
        return $this->respondWithData($slots);
    }
}
