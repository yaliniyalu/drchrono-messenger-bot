<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Domain\Messenger\WebUrlDefaultAction;
use App\Domain\Payload;
use App\Domain\User;
use App\Service\DrChronoProblemImageService;
use App\Service\UrlHelper;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;

class HealthService
{
    private Messenger $messenger;
    private DrChronoProblemImageService $imageService;

    public function __construct(Messenger $messenger, DrChronoProblemImageService $imageService)
    {
        $this->messenger = $messenger;
        $this->imageService = $imageService;
    }

    public function listAllergies($allergies, User $user)
    {
        $list = [];
        foreach ($allergies as $key => $allergy) {
            $doctor = $allergy['doctor']['first_name'] . " " . $allergy['doctor']['last_name'];
            $list[] = ($key + 1) . ". " . $allergy['reaction'] . " (Dr. {$doctor})";
        }

        $message = implode("\n", $list);
        $this->messenger->send()->message($user->getId(), Message::create("😷 Here are your active allergies"));
        $this->messenger->send()->message($user->getId(), Message::create($message));
    }

    public function listProblems($problems, User $user)
    {
        $list = [];
        foreach ($problems as $key => $problem) {
            $doctor = $problem['doctor']['first_name'] . " " . $problem['doctor']['last_name'];

            $element = GenericElement::create($problem['name'])
                ->setSubtitle("Dr. {$doctor} ({$problem['date_diagnosis']})")
                ->setDefaultAction(WebUrlDefaultAction::create('', $problem['info_url']))
                ->setButtons([WebUrl::create("More Info", $problem['info_url'])]);

            $url = $this->imageService->get($problem['info_url']);
            if ($url) {
                $element->setImageUrl($url);
            }

            $list[] = $element;
        }

        $this->messenger->send()->message($user->getId(), Message::create("🤕 Here are your active problems"));
        $this->messenger->send()->message($user->getId(), Message\Attachment\Template\GenericTemplate::create($list));
    }

    public function listMedications($medications, User $user)
    {
        $list = [];
        foreach ($medications as $key => $medication) {
            $doctor = $medication['doctor']['first_name'] . " " . $medication['doctor']['last_name'];
            $url = UrlHelper::webview('/medication/', $medication['id']);

            $list[] = GenericElement::create($medication['name'])
                ->setDefaultAction(WebUrlDefaultAction::create("", $url)
                    ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                    ->setMessengerExtension(true))
                ->setSubtitle("Dr. {$doctor}")
                ->setButtons([
                    WebUrl::create("More Info", $url)
                        ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                        ->setMessengerExtension(true)
                ]);
        }

        $this->messenger->send()->message($user->getId(), Message::create("🤒 Here are your active medications"));
        $this->messenger->send()->message($user->getId(), Message\Attachment\Template\GenericTemplate::create($list));
    }

    public function listLabResults($labResults, User $user)
    {
        $list = [];
        foreach ($labResults as $key => $labResult) {
            if ($labResult['lab_result_value']) {
                $subtitle = $labResult['lab_result_value'];

                if ($labResult['lab_result_value_units']) {
                    $subtitle .= $labResult['lab_result_value_units'];
                }

                if ($labResult['lab_abnormal_flag']) {
                    $subtitle .= " ({$labResult['lab_abnormal_flag']})";
                }
            } else {
                $subtitle = $labResult['lab_order_status'];
            }

            $url = UrlHelper::webview('/lab-result/', $labResult['id']);
            $list[] = GenericElement::create($labResult['title'])
                ->setDefaultAction(WebUrlDefaultAction::create("", $url)
                    ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                    ->setMessengerExtension(true))
                ->setSubtitle($subtitle)
                ->setButtons([
                    WebUrl::create("More Info", $url)
                        ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                        ->setMessengerExtension(true)
                ]);
        }

        $this->messenger->send()->message($user->getId(), Message::create("🧪 Here are your lab results"));
        $this->messenger->send()->message($user->getId(), Message\Attachment\Template\GenericTemplate::create($list));
    }
}
