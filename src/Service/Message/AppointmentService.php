<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Domain\Messenger\WebUrlDefaultAction;
use App\Domain\Payload;
use App\Domain\User;
use App\Service\UrlHelper;
use Exception;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;
use Kerox\Messenger\Model\Message\Attachment\Template\GenericTemplate;

class AppointmentService
{
    private Messenger $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * @param $appointments
     * @param User $user
     * @throws Exception
     */
    public function list(array $appointments, User $user)
    {
        $list = [];

        $webUrl =  UrlHelper::webview("/appointment/new?patient=" . $user->getPatientId());

        $list[] = GenericElement::create("New Appointment")
            ->setDefaultAction(WebUrlDefaultAction::create("", $webUrl)
                ->setMessengerExtension(true))
            ->setSubtitle("Click the button below 👇 to create new appointment")
            ->setButtons([
                WebUrl::create("New Appointment", $webUrl)
                    ->setMessengerExtension(true)
                    ->setFallbackUrl(UrlHelper::webviewFallback())
            ]);

        foreach ($appointments as $appointment) {
            $doctor = $appointment['doctor']['first_name'] . ' ' . $appointment['doctor']['last_name'];
            $webUrl = UrlHelper::webview('/appointment/', $appointment['id']);

            $list[] = GenericElement::create(date("F j, Y, g:i a", strtotime($appointment['scheduled_time'])))
                ->setDefaultAction(WebUrlDefaultAction::create("", $webUrl)
                    ->setMessengerExtension(true)
                    ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL))
                ->setSubtitle('Dr. ' . $doctor)
                ->setButtons([
                    WebUrl::create("View", $webUrl)
                        ->setMessengerExtension(true)
                        ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL),
                    Postback::create("Cancel", Payload::withData(Payload::CANCEL_APPOINTMENT, [$appointment['id']]))
                ]);
        }

        $this->messenger->send()->message($user->getId(), new GenericTemplate($list));
    }

    /**
     * @param array $appointment
     * @param User $user
     * @throws Exception
     */
    public function view(array $appointment, User $user)
    {
        $datetime = date("F j, Y, g:i a", strtotime($appointment['scheduled_time']));
        $doctor = $appointment['doctor']['first_name'] . ' ' . $appointment['doctor']['last_name'];

        $text = [
            "Date/Time: {$datetime}",
            "Doctor: $doctor",
            "Office: {$appointment['office']['name']}",
            "Duration: {$appointment['duration']} Minutes",
            "Reason: {$appointment['reason']}"
        ];

        $message = Message\Attachment\Template\ButtonTemplate::create(implode("\n", $text), [
            WebUrl::create("View Office", $_ENV['WEBVIEW_URL'] . "/office/{$appointment['office']['id']}")
                ->setMessengerExtension(true)
                ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL),
            WebUrl::create("View Doctor", $_ENV['WEBVIEW_URL'] . "/doctor/{$appointment['doctor']['id']}")
                ->setMessengerExtension(true)
                ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL),
            Postback::create("Cancel Appointment", Payload::withData(Payload::CANCEL_APPOINTMENT, [$appointment['id']]))
        ]);

        $this->messenger->send()->message($user->getId(), $message);
    }
}
