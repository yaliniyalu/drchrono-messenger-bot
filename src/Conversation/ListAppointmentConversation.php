<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Service\DrChronoAppointmentService;
use App\Service\Message\AppointmentService;

class ListAppointmentConversation extends AbstractConversation
{
    public function list()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoAppointmentService $service */
        $service = $container->get(DrChronoAppointmentService::class);
        $appointments = $service->list($user->getPatientId());

        if (!count($appointments)) {
            $this->say("📅 There are no upcoming appointments");
        }

        /** @var AppointmentService $service */
        $service = $container->get(AppointmentService::class);
        $service->list($appointments, $user);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->list();
    }
}
