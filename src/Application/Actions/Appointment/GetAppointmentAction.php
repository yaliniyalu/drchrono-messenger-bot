<?php

declare(strict_types=1);

namespace App\Application\Actions\Appointment;

use App\Service\DrChronoAppointmentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetAppointmentAction extends \App\Application\Actions\Action
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
        $id = $this->args['id'];
        $appointment = $this->drChronoAppointmentService->get($id);
        return $this->respondWithData($appointment);
    }
}
