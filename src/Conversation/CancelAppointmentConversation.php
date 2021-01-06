<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Service\DrChronoAppointmentService;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class CancelAppointmentConversation extends AbstractConversation
{
    protected string $appointmentId;

    public function __construct(string $appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    public function confirm()
    {
        $question = Question::create("Are you really want to cancel the appointment?")
            ->addButtons([
                Button::create('Yes')->value('yes'),
                Button::create('No')->value('no'),
            ]);

        $this->ask(
            $question,
            [
                [
                    'pattern' => 'yes|yep|yeah|s',
                    'callback' => function () {
                        $this->cancelAppointment();
                    }
                ],
                [
                    'pattern' => 'nah|no|nope|na',
                    'callback' => function () {
                        $this->bot->reply("Ok");
                    }
                ],
                [
                    'pattern' => '*',
                    'callback' => function () {
                        $this->repeat();
                    }
                ]
            ]
        );
    }

    private function cancelAppointment()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();

        /** @var DrChronoAppointmentService $service */
        $service = $container->get(DrChronoAppointmentService::class);

        try {
            $service->cancel($this->appointmentId);
        } catch (\Exception $e) {
            $this->bot->reply('Sorry, Something went wrong. Unable to cancel the appointment. 😟');
            return;
        }

        $this->bot->reply('✅ Appointment cancelled');
    }

    public function run()
    {
        $this->confirm();
    }
}
