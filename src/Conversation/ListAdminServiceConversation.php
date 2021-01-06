<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Domain\Payload;
use App\Service\DrChronoAdminService;
use App\Service\Message\AdminService;

class ListAdminServiceConversation extends AbstractConversation
{
    protected string $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    private function listDoctors()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();

        /** @var DrChronoAdminService $service */
        $service = $container->get(DrChronoAdminService::class);
        $doctors = $service->listDoctors();

        /** @var AdminService $service */
        $service = $container->get(AdminService::class);
        $service->listDoctors($doctors, $this->getUser());
    }

    private function listOffices()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();

        /** @var DrChronoAdminService $service */
        $service = $container->get(DrChronoAdminService::class);
        $offices = $service->listOffices();

        /** @var AdminService $service */
        $service = $container->get(AdminService::class);
        $service->listOffices($offices, $this->getUser());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        switch ($this->payload) {
            case Payload::LIST_DOCTORS:
                $this->listDoctors();
                break;

            case Payload::LIST_OFFICES:
                $this->listOffices();
                break;
        }
    }
}
