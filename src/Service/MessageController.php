<?php

declare(strict_types=1);

namespace App\Service;

use App\Conversation\CancelAppointmentConversation;
use App\Conversation\HealthConversation;
use App\Conversation\ListAdminServiceConversation;
use App\Conversation\ListAppointmentConversation;
use App\Conversation\ViewAppointmentConversation;
use App\Domain\Payload;
use App\Domain\User;
use App\Service\Message\MenuService;
use BotMan\BotMan\BotMan;
use Psr\Container\ContainerInterface;

class MessageController
{
    private static ContainerInterface $container;

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public function payloadListAppointments(BotMan $bot)
    {
        $bot->startConversation(new ListAppointmentConversation());
    }

    public function payloadCancelAppointment(BotMan $bot, $id)
    {
        $bot->startConversation(new CancelAppointmentConversation($id));
    }

    public function payloadViewAppointment(BotMan $bot, $id)
    {
        $bot->startConversation(new ViewAppointmentConversation($id));
    }

    public function payloadListDoctors(BotMan $bot)
    {
        $bot->startConversation(new ListAdminServiceConversation(Payload::LIST_DOCTORS));
    }

    public function payloadListOffices(BotMan $bot)
    {
        $bot->startConversation(new ListAdminServiceConversation(Payload::LIST_OFFICES));
    }

    public function payloadHealthMenu(BotMan $bot)
    {
        $user = $bot->getMessage()->getExtras('user');

        /** @var MenuService $service */
        $service = self::$container->get(MenuService::class);
        $service->showHealthMenu($user);
    }

    public function payloadListAllergies(BotMan $bot)
    {
        $bot->startConversation(new HealthConversation(Payload::LIST_ALLERGIES));
    }

    public function payloadListProblems(BotMan $bot)
    {
        $bot->startConversation(new HealthConversation(Payload::LIST_PROBLEMS));
    }

    public function payloadListMedications(BotMan $bot)
    {
        $bot->startConversation(new HealthConversation(Payload::LIST_MEDICATIONS));
    }

    public function payloadListLabResults(BotMan $bot)
    {
        $bot->startConversation(new HealthConversation(Payload::LIST_LAB_RESULTS));
    }

    public function payloadMainMenu(BotMan $bot)
    {
        $this->showMenu($bot);
    }

    public function payloadLogout(BotMan $bot)
    {
        /** @var User $user */
        $user = $bot->getMessage()->getExtras('user');

        /** @var UserService $service */
        $service = self::$container->get(UserService::class);
        $service->delete($user->getId());

        $bot->reply("You are logged out successfully");
        $bot->reply("Bye");
    }

    public function payloadHelp(BotMan $bot)
    {
        $message = "Help\n";
        $message .= "-----\n";
        $message .= "Send 'menu' to get menu\n";
        $message .= "Send 'logout' to logout\n";
        $message .= "Send 'help' to get help";
        $bot->reply($message);
    }

    public function showMenu(BotMan $bot)
    {
        $user = $bot->getMessage()->getExtras('user');

        /** @var MenuService $service */
        $service = self::$container->get(MenuService::class);
        $service->showMenu($user);
    }

    public function processObject(BotMan $bot, string $object): bool
    {
        switch ($object) {
            case 'appointment':
                $this->payloadListAppointments($bot);
                return true;

            case 'menu':
                $this->payloadMainMenu($bot);
                return true;

            case 'logout':
                $this->payloadLogout($bot);
                return true;

            case 'help':
                $this->payloadHelp($bot);
                return true;

            case 'health':
                $this->payloadHealthMenu($bot);
                return true;

            case 'doctor':
                $this->payloadListDoctors($bot);
                return true;

            case 'office':
                $this->payloadListOffices($bot);
                return true;

            case 'lab result':
                $this->payloadListLabResults($bot);
                return true;

            case 'allergy':
                $this->payloadListAllergies($bot);
                return true;

            case 'problem':
                $this->payloadListProblems($bot);
                return true;

            case 'medication':
                $this->payloadListMedications($bot);
                return true;

            default:
                return false;
        }
    }
}
