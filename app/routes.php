<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->group('/webhook/messenger/callback', function (Group $group) {
        $group->get('', \App\Application\Actions\Webhook\MessengerCallbackValidateAction::class);
        $group->post('', \App\Application\Actions\Webhook\MessengerCallbackAction::class)
            ->add(\App\Application\Middleware\ValidateMessengerWebhookCallback::class);
    });

    $app->group('/webhook/drchrono/callback', function (Group $group) {
        $group->get('', \App\Application\Actions\Webhook\DrChronoCallbackValidateAction::class);
        $group->post('', \App\Application\Actions\Webhook\DrChronoCallbackAction::class);
    });

    $app->group('/web', function (Group $group) {
        $group->get('/privacy', null);
        $group->get('/terms', null);
    });

    $app->group('/api', function (Group $group) {
        $group->get('/appointment/slots', \App\Application\Actions\Appointment\GetFreeSlotsAction::class);
        $group->get('/appointment/{id}', \App\Application\Actions\Appointment\GetAppointmentAction::class);
        $group->post('/appointment', \App\Application\Actions\Appointment\AddAppointmentAction::class);

        $group->get('/office', \App\Application\Actions\Office\ListOfficeAction::class);
        $group->get('/office/{id}', \App\Application\Actions\Office\GetOfficeAction::class);
        $group->get('/doctor', \App\Application\Actions\Doctor\ListDoctorAction::class);
        $group->get('/doctor/{id}', \App\Application\Actions\Doctor\GetDoctorAction::class);
        $group->get('/medication/{id}', \App\Application\Actions\Medication\GetMedicationAction::class);
        $group->get('/lab-result/{id}', \App\Application\Actions\LabResult\GetLabResult::class);
    });
};
