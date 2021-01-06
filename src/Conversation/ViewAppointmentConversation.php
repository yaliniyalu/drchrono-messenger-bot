<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Service\DrChronoAppointmentService;
use App\Service\Message\AppointmentService;

class ViewAppointmentConversation extends AbstractConversation
{
    protected string $appointmentId;

    public function __construct(string $appointmentId)
    {
        $this->appointmentId = $appointmentId;
    }

    public function view()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoAppointmentService $service */
        $service = $container->get(DrChronoAppointmentService::class);
        $appointment = $service->get($this->appointmentId);

        /** @var AppointmentService $service */
        $service = $container->get(AppointmentService::class);
        $service->view($appointment, $user);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->view();
    }
}
