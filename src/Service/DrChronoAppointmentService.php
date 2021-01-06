<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;

class DrChronoAppointmentService
{
    private Client $http;
    private DrChronoAdminService $adminService;

    public function __construct(DrChronoClient $client, DrChronoAdminService $adminService)
    {
        $this->http = $client->getHttp();
        $this->adminService = $adminService;
    }

    public function list(int $patientId): array
    {
        $response = $this->http
            ->get('appointments', [
                'query' => [
                    'patient' => $patientId,
                    'date_range' => date('Y-m-d') . '/' . date('Y-m-d', time() + (60 * 24 * 60 * 60))
                ]
            ]);

        $results = json_decode((string) $response->getBody(), true)['results'];

        $results = array_filter($results, fn($val) => $val['deleted_flag'] == false &&
            !in_array($val['status'], ['Rescheduled', 'Cancelled', 'No Show']));

        array_walk($results, function (&$val) {
            $val['doctor'] = $this->adminService->getDoctor($val['doctor']);
            $val['office'] = $this->adminService->getOffice($val['office']);
        });

        return $results;
    }

    public function cancel(string $id)
    {
        $this->http
            ->patch("appointments/$id", [
                'json' => [
                    'status' => 'Cancelled'
                ]
            ]);
    }

    public function get(string $id): array
    {
        $response = $this->http->get("appointments/$id");

        $result = json_decode((string) $response->getBody(), true);

        $result['doctor'] = $this->adminService->getDoctor($result['doctor']);
        $result['office'] = $this->adminService->getOffice($result['office']);

        return $result;
    }

    public function getFreeSlots(int $office, string $date, int $duration): array
    {
        $office = $this->adminService->getOffice($office);
        $rooms = [];
        foreach ($office['exam_rooms'] as $exam_room) {
            if (!$exam_room['online_scheduling']) {
                continue;
            }

            $rooms[] = ['id' => $exam_room['index'], 'name' => $exam_room['name']];
        }

        if (!count($rooms)) {
            return [];
        }

        $response = $this->http
            ->get('appointments', [
                'query' => [
                    'office' => $office,
                    'date' => $date
                ]
            ]);

        $results = json_decode((string) $response->getBody(), true)['results'];

        $apt = [];
        foreach ($results as $result) {
            if (
                $result['deleted_flag'] == false &&
                !in_array($result['status'], ['Rescheduled', 'Cancelled', 'No Show']) &&
                $result['allow_overlapping'] == false && $result['scheduled_time']
            ) {
                $from = strtotime($result['scheduled_time']);
                $dr = 60 * ($result['duration'] ?? 30);
                $apt[$result['exam_room']][] = [
                    'from' => $from,
                    'to' => $from + $dr
                ];
            }
        }

        $start_time = strtotime($date . 'T' . $office['start_time']);
        $end_time = strtotime($date . 'T' . $office['end_time']);

        if ($start_time == $end_time) {
            $end_time = $end_time + (23.5 * 60 * 60);
        }

        if ($start_time > $end_time) {
            $tmp = $start_time;
            $start_time = $end_time;
            $end_time = $tmp;
        }

        $add_min  = $duration * 60;

        foreach ($rooms as &$room) {
            $slot_start_time = $start_time;
            $slots = [];

            while ($slot_start_time <= $end_time) {
                $slot_end_time = $slot_start_time + $add_min;

                foreach ($apt[$room['id']]  ?? [] as $key => $item) {
                    if ($item['from'] >= $slot_start_time && $item['from'] <= $slot_end_time) {
                        $slot_start_time = $item['to'];
                        unset($apt[$room['id']][$key]);
                        continue 2;
                    }
                }

                $slots[] = ['from' => date("H:i", $slot_start_time), 'to' => date("H:i", $slot_end_time)];

                $slot_start_time = $slot_end_time;
            }

            $room['slots'] = $slots;
        }

        return $rooms;
    }

    public function add(array $data)
    {
        $this->http
            ->post('appointments', [
                "json" => [
                    'doctor' => $data['doctor'],
                    'office' => $data['office'],
                    'patient' => $data['patient'],
                    'scheduled_time' => $data['date'],
                    'duration' => $data['duration'],
                    'exam_room' => $data['room'],
                    'reason' => $data['reason']
                ]
            ]);
    }
}
