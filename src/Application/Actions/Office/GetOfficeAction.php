<?php

declare(strict_types=1);

namespace App\Application\Actions\Office;

use App\Service\DrChronoAdminService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetOfficeAction extends \App\Application\Actions\Action
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
        $office = $this->drChronoAdminService->getOffice($id);
        return $this->respondWithData($office);
    }
}
