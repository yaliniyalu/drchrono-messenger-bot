<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\User;

class UserService
{
    private \MysqliDb $db;

    public function __construct(\MysqliDb $db)
    {
        $this->db = $db;
    }

    public function get(string $id): User
    {
        $data = $this->db
            ->where('id', $id)
            ->getOne('users');

        if (!$this->db->count) {
            throw new DomainRecordNotFoundException("User not found");
        }

        $user = new User($id, $data['name']);
        $user->setPatientId($data['patient_id']);
        $user->setPatientName($data['patient_name']);
        $user->setState(json_decode($data['state']));

        return $user;
    }

    public function getPatient(int $id): User
    {
        $data = $this->db
            ->where('patient_id', $id)
            ->getOne('users');

        if (!$this->db->count) {
            throw new DomainRecordNotFoundException("Patient not found");
        }

        $user = new User($data['id'], $data['name']);
        $user->setPatientId($data['patient_id']);
        $user->setPatientName($data['patient_name']);
        $user->setState(json_decode($data['state']));

        return $user;
    }

    public function save(User $user): void
    {
        $data = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'patient_id' => $user->getPatientId(),
            'patient_name' => $user->getPatientName(),
            'state' => json_encode($user->getState())
        ];

        $this->db
            ->onDuplicate(['name', 'patient_id', 'patient_name', 'state'])
            ->insert('users', $data);
    }

    public function delete(string $id)
    {
        $this->db
            ->where('id', $id)
            ->delete('users', 1);
    }
}
