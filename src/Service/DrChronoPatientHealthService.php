<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;

class DrChronoPatientHealthService
{
    private Client $http;
    private DrChronoAdminService $adminService;

    public function __construct(DrChronoClient $client, DrChronoAdminService $adminService)
    {
        $this->http = $client->getHttp();
        $this->adminService = $adminService;
    }

    public function listAllergies(int $patient): array
    {
        return $this->queryHealth('allergies', $patient);
    }

    public function listProblems(int $patient): array
    {
        return $this->queryHealth('problems', $patient);
    }

    public function listMedications(int $patient): array
    {
        return $this->queryHealth('medications', $patient);
    }

    public function listLabResults(int $patient): array
    {
        $response = $this->http
            ->get('patient_lab_results', [
                'query' => [
                    'patient' => $patient
                ]
            ]);

        return json_decode((string) $response->getBody(), true)['results'];
    }

    public function getMedication(int $id): array
    {
        $response = $this->http
            ->get("medications/{$id}");

        $medication = json_decode((string) $response->getBody(), true);
        $medication['doctor'] = $this->adminService->getDoctor($medication['doctor']);

        return $medication;
    }

    public function getLabResult(int $id): array
    {
        $response = $this->http
            ->get("patient_lab_results/{$id}");

        $labResult = json_decode((string) $response->getBody(), true);
        $labResult['ordering_doctor'] = $this->adminService->getDoctor($labResult['ordering_doctor']);
        return $labResult;
    }

    public function queryHealth(string $topic, int $patient): array
    {
        $response = $this->http
            ->get($topic, [
                'query' => [
                    'patient' => $patient
                ]
            ]);

        $results = json_decode((string) $response->getBody(), true)['results'];
        $results = array_filter($results, fn($val) => $val['status'] == 'active');

        array_walk($results, function (&$val) {
            $val['doctor'] = $this->adminService->getDoctor($val['doctor']);
        });

        return $results;
    }
}
