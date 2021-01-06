<?php

declare(strict_types=1);

namespace App\Application\Actions\Doctor;

use App\Service\DrChronoAdminService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetDoctorAction extends \App\Application\Actions\Action
{
    private DrChronoAdminService $drChronoAdminService;

    public function __construct(LoggerInterface $logger, DrChronoAdminService $drChronoAdminService)
    {
        parent::__construct($logger);
        $this->drChronoAdminService = $drChronoAdminService;
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $id = (int) $this->args['id'];
        $doctors = $this->drChronoAdminService->getDoctor($id);
        return $this->respondWithData($doctors);
    }
}
