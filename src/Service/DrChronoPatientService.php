<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DrChronoPatientService
{
    private Client $http;

    public function __construct(DrChronoClient $client)
    {
        $this->http = $client->getHttp();
    }

    public function findPatient(string $email, string $dob, string $mobile)
    {
        $response = $this->http
            ->get('patients', [
                'query' => [
                    'email' => $email,
                    'date_of_birth' => $dob
                ]
            ]);

        $data = json_decode((string) $response->getBody(), true)['results'];

        if (!count($data)) {
            return null;
        }

        if ($data[0]['cell_phone'] === $mobile) {
            return $data[0];
        }

        return null;
    }

    public function createPatient(
        string $f_name,
        string $l_name,
        string $gender,
        string $dob,
        string $email,
        string $mobile,
        int $doctor
    ): array {
        try {
            $response = $this->http
                ->post('patients', [
                    'json' => [
                        'first_name' => $f_name,
                        'last_name' => $l_name,
                        'gender' => $gender,
                        'dob' => $dob,
                        'email' => $email,
                        'mobile' => $mobile,
                        'doctor' => $doctor
                    ]
                ]);
            return json_decode((string) $response->getBody(), true);
        } catch (GuzzleException $e) {
            $response = $e->getResponse();
            $data = json_decode((string) $response->getBody(), true);
            $errors = [];
            foreach ($data as $key => $datum) {
                $errors[] = $key . ":\n" . implode("\n", $datum);
            }
            throw new \Exception(implode("\n", $errors));
        }
    }
}
