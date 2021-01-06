<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Domain\Payload;
use App\Domain\User;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\Message;
use Kerox\Messenger\Model\Message\Attachment\Template\Element\GenericElement;
use Kerox\Messenger\Model\Message\Attachment\Template\GenericTemplate;

class MenuService
{
    private Messenger $messenger;

    public function __construct(Messenger $messenger)
    {
        $this->messenger = $messenger;
    }

    public function showMenu(User $user)
    {
        $menus = [
            [
                'title' => 'Appointments',
                'subtitle' => 'View your upcoming appointments',
                'webview' => null,
                'image' => 'menu_appointment.jpg',
                'button' => [
                    'title' => 'View',
                    'payload' => Payload::LIST_APPOINTMENTS
                ]
            ],
            [
                'title' => 'Health',
                'subtitle' => 'Your health related services menu',
                'webview' => null,
                'image' => 'menu_health.jpg',
                'button' => [
                    'title' => 'Get Health Menu',
                    'payload' => Payload::HEALTH_MENU
                ]
            ],
            [
                'title' => 'Doctors',
                'subtitle' => 'Our working doctors',
                'webview' => null,
                'image' => 'menu_doctor.jpg',
                'button' => [
                    'title' => 'View',
                    'payload' => Payload::LIST_DOCTORS
                ]
            ],
            [
                'title' => 'Offices',
                'subtitle' => 'Our offices',
                'webview' => null,
                'image' => 'menu_office.jpg',
                'button' => [
                    'title' => 'View',
                    'payload' => Payload::LIST_OFFICES
                ]
            ],
        ];
        $this->renderMenu($menus, $user);
    }

    public function showHealthMenu(User $user)
    {
        $menus = [
            [
                'title' => 'Medications',
                'subtitle' => 'My active medications',
                'webview' => '/medication',
                'image' => 'health_medication.jpg',
                'button' => [
                    'title' => 'View Medications',
                    'payload' => Payload::LIST_MEDICATIONS
                ]
            ],
            [
                'title' => 'Allergies',
                'subtitle' => 'My active allergies',
                'webview' => '/allergies',
                'image' => 'health_allergy.jpg',
                'button' => [
                    'title' => 'View Allergies',
                    'payload' => Payload::LIST_ALLERGIES
                ]
            ],
            [
                'title' => 'Problems',
                'subtitle' => 'My active Problems',
                'webview' => '/problems',
                'image' => 'health_problem.jpg',
                'button' => [
                    'title' => 'View Problems',
                    'payload' => Payload::LIST_PROBLEMS
                ]
            ],
            [
                'title' => 'Lab Results',
                'subtitle' => 'My Lab Results',
                'webview' => '/lab-results',
                'image' => 'health_lab_result.jpg',
                'button' => [
                    'title' => 'My Lab Results',
                    'payload' => Payload::LIST_LAB_RESULTS
                ]
            ],
        ];
        $this->renderMenu($menus, $user);
    }

    private function renderMenu($menus, User $user)
    {
        $list = [];
        foreach ($menus as $menu) {
            $list[] = GenericElement::create($menu['title'])
//            ->setDefaultAction(WebUrlDefaultAction::create("", $_ENV['WEBVIEW_URL'] . $menu['webview))
                ->setSubtitle($menu['subtitle'])
                ->setImageUrl($_ENV['WEB_URL'] . '/assets/images/' . $menu['image'])
                ->setButtons([
                    Postback::create($menu['button']['title'], $menu['button']['payload'])
                ]);
        }

        $this->messenger->send()->message($user->getId(), new GenericTemplate($list));
    }
}
