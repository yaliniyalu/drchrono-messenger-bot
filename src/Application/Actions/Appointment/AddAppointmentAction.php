<?php

declare(strict_types=1);

namespace App\Application\Actions\Appointment;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Service\DrChronoAppointmentService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AddAppointmentAction extends \App\Application\Actions\Action
{
    private DrChronoAppointmentService $drChronoAppointmentService;

    public function __construct(
        LoggerInterface $logger,
        DrChronoAppointmentService $drChronoAppointmentService
    ) {
        parent::__construct($logger);

        $this->drChronoAppointmentService = $drChronoAppointmentService;
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $body = (array) $this->getFormData();

        if (
            empty($body['doctor']) ||
            empty($body['patient']) ||
            empty($body['office']) ||
            empty($body['room']) || empty($body['date']) || empty($body['duration']) || empty($body['reason'])
        ) {
            return $this->respond(new ActionPayload(
                StatusCodeInterface::STATUS_BAD_REQUEST,
                [],
                new ActionError("InvalidArgument", "Invalid Argument")
            ));
        }

        $this->drChronoAppointmentService->add($body);

        return $this->respondWithData($body);
    }
}
