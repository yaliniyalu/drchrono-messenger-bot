<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Payload;
use Exception;
use Kerox\Messenger\Exception\MessengerException;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\ProfileSettings;
use Kerox\Messenger\Model\ProfileSettings\Greeting;
use Kerox\Messenger\Model\ProfileSettings\PersistentMenu;

class ProfileService
{
    private Messenger $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * @throws MessengerException
     * @throws Exception
     */
    public function setProfile(): void
    {
        $persistentMenus = [
            /*PersistentMenu::create()
                ->addButtons([
                    Postback::create('Main Menu', Payload::MAIN_MENU),
                    Postback::create('My Appointments', Payload::LIST_APPOINTMENTS),
                    Postback::create('Health Menu', Payload::HEALTH_MENU),
                    Postback::create('Doctors', Payload::LIST_DOCTORS),
                ]),*/
        ];

        $greetings = [
            Greeting::create('Hello!')
        ];

        $profileSettings = ProfileSettings::create()
            ->addWhitelistedDomains([
                $_ENV['WEBVIEW_URL']
            ])
//            ->addPersistentMenus($persistentMenus)
            ->addStartButton(Payload::GET_STARTED)
            ->addGreetings($greetings);

        $this->messenger->profile()->add($profileSettings);
    }

    /**
     * @throws MessengerException
     */
    public function deleteProfile(): void
    {
        $this->messenger->profile()->delete(['persistent_menu', 'greeting', 'get_started']);
    }

    /**
     * @throws MessengerException
     */
    public function getProfile(): \Kerox\Messenger\Response\ProfileResponse
    {
        return $this->messenger->profile()->get(['persistent_menu', 'greeting', 'get_started', 'whitelisted_domains']);
    }
}
