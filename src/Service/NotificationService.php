<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\User;
use Kerox\Messenger\Api\Send;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message\Attachment\Template\ButtonTemplate;

class NotificationService
{
    private DrChronoAdminService $drChronoAdminService;
    private Messenger $messenger;
    private UserService $userService;

    public function __construct(
        Messenger $messenger,
        DrChronoAdminService $drChronoAdminService,
        UserService $userService
    ) {
        $this->drChronoAdminService = $drChronoAdminService;
        $this->messenger = $messenger;
        $this->userService = $userService;
    }

    public function onAppointmentCreated(array $app)
    {
        $patient = $this->userService->getPatient($app['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($app['doctor']);

        $time = date('F j, Y h:i a', strtotime($app['scheduled_time']));
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "New appointment has been scheduled with Dr. {$doctor_name} on {$time}";
        $this->messenger->send()->message($patient->getId(), ButtonTemplate::create($message, [
            WebUrl::create("View", UrlHelper::webview('/appointment/', $app['id']))
                ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                ->setMessengerExtension(true)
        ]), [
            Send::OPTION_MESSAGING_TYPE => Send::MESSAGING_TYPE_MESSAGE_TAG,
            Send::OPTION_TAG => Send::TAG_CONFIRMED_EVENT_UPDATE
        ]);
    }

    public function onAppointmentModified(array $app)
    {
    }

    public function onPatientAllergyCreated(array $allergy)
    {
        $patient = $this->userService->getPatient($allergy['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($allergy['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "New allergy 😷 have been added to your medical record by Dr. {$doctor_name}.\n\n";
        $message .= "{$allergy['reaction']}";

        $this->send($patient, $message);
    }

    public function onPatientAllergyModified(array $allergy)
    {
        $patient = $this->userService->getPatient($allergy['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($allergy['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "Your allergy 😷 have been modified by Dr. {$doctor_name}.\n\n";
        $message .= "{$allergy['reaction']}\n";
        $message .= "Status: {$allergy['status']}\n";

        $this->send($patient, $message);
    }

    public function onPatientProblemCreated(array $problem)
    {
        $patient = $this->userService->getPatient($problem['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($problem['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "New problem 🤕 have been added to your medical record by Dr. {$doctor_name}.\n\n";
        $message .= "{$problem['name']}";

        $this->send($patient, $message);
    }

    public function onPatientProblemModified(array $problem)
    {
        $patient = $this->userService->getPatient($problem['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($problem['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "Your problem 🤕 have been modified by Dr. {$doctor_name}.\n\n";
        $message .= "{$problem['name']}\n";
        $message .= "Status: {$problem['status']}";

        $this->send($patient, $message);
    }

    public function onPatientMedicationCreated(array $medication)
    {
        $patient = $this->userService->getPatient($medication['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($medication['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "New medication 🤒 have been added to your medical record by Dr. {$doctor_name}.\n\n";
        $message .= "{$medication['name']}";

        $message = ButtonTemplate::create($message, [
            WebUrl::create("View", UrlHelper::webview('/medication/', $medication['id']))
                ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                ->setMessengerExtension(true)
        ]);

        $this->send($patient, $message);
    }

    public function onPatientMedicationModified(array $medication)
    {
        $patient = $this->userService->getPatient($medication['patient']);
        $doctor = $this->drChronoAdminService->getDoctor($medication['doctor']);
        $doctor_name = $doctor['first_name'] . " " . $doctor['last_name'];

        $message = "Your medication 🤒 have been modified by Dr. {$doctor_name}.\n\n";
        $message .= "{$medication['name']}";

        $message = ButtonTemplate::create($message, [
            WebUrl::create("View", UrlHelper::webview('/medication/', $medication['id']))
                ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                ->setMessengerExtension(true)
        ]);

        $this->send($patient, $message);
    }

    private function send(User $patient, $message)
    {
        $this->messenger->send()->message($patient->getId(), $message, [
            Send::OPTION_MESSAGING_TYPE => Send::MESSAGING_TYPE_MESSAGE_TAG,
            Send::OPTION_TAG => Send::TAG_ACCOUNT_UPDATE
        ]);
    }
}
