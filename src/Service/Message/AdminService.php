<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Domain\Messenger\WebUrlDefaultAction;
use App\Domain\User;
use App\Service\UrlHelper;
use Exception;
use Kerox\Messenger\Exception\MessengerException;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\PhoneNumber;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;
use Kerox\Messenger\Model\Message\Attachment\Template\GenericTemplate;

class AdminService
{
    private Messenger $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * @param array $doctors
     * @param User $user
     * @throws MessengerException
     * @throws Exception
     */
    public function listDoctors(array $doctors, User $user)
    {
        $list = [];
        foreach ($doctors as $doctor) {
            $url = UrlHelper::webview('/doctor/', $doctor['id']);

            $list[] = GenericElement::create($doctor['first_name'] . ' ' . $doctor['last_name'])
                ->setSubtitle($doctor['job_title'])
                ->setDefaultAction(WebUrlDefaultAction::create("", $url)
                    ->setMessengerExtension(true)
                    ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL))
                ->setImageUrl($doctor['profile_picture'])
                ->setButtons([
                    PhoneNumber::create('Call', preg_replace('/[^0-9]/', '', $doctor['cell_phone'])),
                    WebUrl::create('More Info', $url)
                        ->setMessengerExtension(true)
                        ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                ]);
        }

        $this->messenger->send()->message($user->getId(), GenericTemplate::create($list));
    }

    /**
     * @param array $offices
     * @param User $user
     * @throws MessengerException
     * @throws Exception
     */
    public function listOffices(array $offices, User $user)
    {
        $list = [];
        foreach ($offices as $office) {
            $url = UrlHelper::webview('/office/', $office['id']);

            $list[] = GenericElement::create($office['name'])
                ->setSubtitle($office['city'] ?? '')
                ->setDefaultAction(WebUrlDefaultAction::create("", $url)
                    ->setMessengerExtension(true)
                    ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL))
                ->setButtons([
                    PhoneNumber::create('Call', preg_replace('/[^0-9]/', '', $office['phone_number'])),
                    WebUrl::create('More Info', $url)
                        ->setMessengerExtension(true)
                        ->setWebviewHeightRatio(WebUrl::RATIO_TYPE_TALL)
                ]);
        }

        $this->messenger->send()->message($user->getId(), GenericTemplate::create($list));
    }
}
