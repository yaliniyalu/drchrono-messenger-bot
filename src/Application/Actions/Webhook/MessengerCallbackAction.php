<?php

declare(strict_types=1);

namespace App\Application\Actions\Webhook;

use App\Conversation\AbstractConversation;
use App\Conversation\CancelAppointmentConversation;
use App\Conversation\HealthConversation;
use App\Conversation\ListAdminServiceConversation;
use App\Conversation\ListAppointmentConversation;
use App\Conversation\Middleware\IsLoggedInMiddleware;
use App\Conversation\Middleware\OnMessageReceived;
use App\Conversation\OnboardConversation;
use App\Conversation\ViewAppointmentConversation;
use App\Domain\Payload;
use App\Domain\WitNlp;
use App\Service\Message\MenuService;
use App\Service\MessageController;
use App\Service\UserService;
use BotMan\BotMan\BotMan;
use Fig\Http\Message\StatusCodeInterface;
use Kerox\Messenger\Messenger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class MessengerCallbackAction extends \App\Application\Actions\Action
{
    private BotMan $bot;
    private UserService $userService;
    private ContainerInterface $container;
    private Messenger $messenger;

    public function __construct(
        LoggerInterface $logger,
        BotMan $botMan,
        UserService $userService,
        ContainerInterface $container,
        Messenger $messenger
    ) {
        parent::__construct($logger);

        $this->bot = $botMan;
        $this->userService = $userService;
        $this->container = $container;
        $this->messenger = $messenger;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function action(): Response
    {
        AbstractConversation::setContainer($this->container);
        MessageController::setContainer($this->container);

        error_log(file_get_contents("php://input"));

        $this->bot->group(['middleware' => new IsLoggedInMiddleware()], function (BotMan $bot) {

            $bot->hears(Payload::GET_STARTED, MessageController::class . "@payloadMainMenu");

            //<editor-fold desc="Appointment">
            $bot->hears(Payload::LIST_APPOINTMENTS, MessageController::class . "@payloadListAppointments");

            $bot->hears(
                Payload::withPlaceholder(Payload::CANCEL_APPOINTMENT, ['id']),
                MessageController::class . "@payloadCancelAppointment"
            );

            $bot->hears(
                Payload::withPlaceholder(Payload::VIEW_APPOINTMENT, ['id']),
                MessageController::class . "@payloadViewAppointment"
            );
            //</editor-fold>

            $bot->hears(Payload::LIST_DOCTORS, MessageController::class . "@payloadListDoctors");
            $bot->hears(Payload::LIST_OFFICES, MessageController::class . "@payloadListOffices");

            //<editor-fold desc="Health">
            $this->bot->hears(Payload::HEALTH_MENU, MessageController::class . "@payloadHealthMenu");
            $this->bot->hears(Payload::LIST_ALLERGIES, MessageController::class . "@payloadListAllergies");
            $this->bot->hears(Payload::LIST_PROBLEMS, MessageController::class . "@payloadListProblems");
            $this->bot->hears(Payload::LIST_MEDICATIONS, MessageController::class . "@payloadListMedications");
            $this->bot->hears(Payload::LIST_LAB_RESULTS, MessageController::class . "@payloadListLabResults");
            //</editor-fold>

            $this->bot->hears(Payload::MAIN_MENU, MessageController::class . "@payloadMainMenu");
            $this->bot->hears(Payload::LOGOUT, MessageController::class . "@payloadLogout");
            $this->bot->hears(Payload::HELP, MessageController::class . "@payloadHelp");
        });

        $this->bot->fallback(function (BotMan $bot) {
            $user = $bot->getMessage()->getExtras('user');

            if (!$user->getPatientId()) {
                $bot->startConversation(new OnboardConversation());
                return;
            }

            $nlp = $bot->getMessage()->getExtras('nlp');
            $nlp = new WitNlp($nlp);

            if ($nlp->isThanks()) {
                $bot->reply("You are welcome 🤗");
                return;
            }

            if ($nlp->isBye()) {
                $bot->reply("Bye");
                $bot->reply("Have a nice day 😊");
                return;
            }

            if ($nlp->isGreetings()) {
                $bot->reply('Hi');
                $bot->reply('Nice to see you again 😊');
                $bot->reply('Here is your menu 📖');

                /** @var MenuService $service */
                $service = $this->container->get(MenuService::class);
                $service->showMenu($user);
                return;
            }

            $object = $nlp->getEntityValue('object:object');
//            $action = $nlp->getEntityValue('action:action');

            if ($object) {
                $result = (new MessageController())->processObject($bot, $object);
                if ($result) {
                    return;
                }
            }

            $bot->randomReply(['🤔', 'I cannot understand what you said 😔', 'Can you rephrase it']);
        });

        $this->bot->middleware->received($this->container->get(OnMessageReceived::class));

        $this->bot->listen();

        return $this->response->withStatus(StatusCodeInterface::STATUS_OK);
    }
}
