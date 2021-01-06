<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Domain\Payload;
use App\Service\DrChronoPatientHealthService;
use App\Service\Message\HealthService;

class HealthConversation extends AbstractConversation
{
    protected string $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    private function listAllergies()
    {
        $this->markSeenAndStartTyping();
        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoPatientHealthService $service */
        $service = $container->get(DrChronoPatientHealthService::class);
        $allergies = $service->listAllergies($user->getPatientId());

        if (!count($allergies)) {
            $this->say("There are no active allergies. 😊");
            return;
        }

        /** @var HealthService $service */
        $service = $container->get(HealthService::class);
        $service->listAllergies($allergies, $user);
    }

    private function listProblems()
    {
        $this->markSeenAndStartTyping();
        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoPatientHealthService $service */
        $service = $container->get(DrChronoPatientHealthService::class);
        $allergies = $service->listProblems($user->getPatientId());

        if (!count($allergies)) {
            $this->say("There are no active problems 😊");
            return;
        }

        /** @var HealthService $service */
        $service = $container->get(HealthService::class);
        $service->listProblems($allergies, $user);
    }

    private function listMedications()
    {
        $this->markSeenAndStartTyping();
        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoPatientHealthService $service */
        $service = $container->get(DrChronoPatientHealthService::class);
        $allergies = $service->listMedications($user->getPatientId());

        if (!count($allergies)) {
            $this->say("There are no active medications 😊");
            return;
        }

        /** @var HealthService $service */
        $service = $container->get(HealthService::class);
        $service->listMedications($allergies, $user);
    }

    private function listLabResults()
    {
        $this->markSeenAndStartTyping();
        $container = $this->getContainer();
        $user = $this->getUser();

        /** @var DrChronoPatientHealthService $service */
        $service = $container->get(DrChronoPatientHealthService::class);
        $allergies = $service->listLabResults($user->getPatientId());

        if (!count($allergies)) {
            $this->say("There are no lab results 🧪");
            return;
        }

        /** @var HealthService $service */
        $service = $container->get(HealthService::class);
        $service->listLabResults($allergies, $user);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        switch ($this->payload) {
            case Payload::LIST_ALLERGIES:
                $this->listAllergies();
                break;

            case Payload::LIST_PROBLEMS:
                $this->listProblems();
                break;

            case Payload::LIST_MEDICATIONS:
                $this->listMedications();
                break;

            case Payload::LIST_LAB_RESULTS:
                $this->listLabResults();
                break;
        }
    }
}
