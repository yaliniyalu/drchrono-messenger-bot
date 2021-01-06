<?php

declare(strict_types=1);

namespace App\Application\Actions\Webhook;

use App\Application\Actions\Action;
use App\Service\NotificationService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class DrChronoCallbackAction extends Action
{
    private NotificationService $notificationService;

    public function __construct(LoggerInterface $logger, NotificationService $notificationService)
    {
        parent::__construct($logger);
        $this->notificationService = $notificationService;
    }

    protected function action(): Response
    {
        $event = $this->request->getHeader('X-drchrono-event')[0];
        $data = $this->getFormData();

        try {
            switch ($event) {
                case 'APPOINTMENT_CREATE':
                    $this->notificationService->onAppointmentCreated($data['object']);
                    break;

                case 'APPOINTMENT_DELETE':
                case 'APPOINTMENT_MODIFY':
                    break;

                case 'PATIENT_ALLERGY_CREATE':
                    $this->notificationService->onPatientAllergyCreated($data['object']);
                    break;

                case 'PATIENT_ALLERGY_MODIFY':
                    $this->notificationService->onPatientAllergyModified($data['object']);
                    break;

                case 'PATIENT_PROBLEM_CREATE':
                    $this->notificationService->onPatientProblemCreated($data['object']);
                    break;

                case 'PATIENT_PROBLEM_MODIFY':
                    $this->notificationService->onPatientProblemModified($data['object']);
                    break;

                case 'PATIENT_MEDICATION_CREATE':
                    $this->notificationService->onPatientMedicationCreated($data['object']);
                    break;

                case 'PATIENT_MEDICATION_MODIFY':
                    $this->notificationService->onPatientMedicationModified($data['object']);
                    break;
            }
        } catch (\Exception $e) {
        }

        return $this->response->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
