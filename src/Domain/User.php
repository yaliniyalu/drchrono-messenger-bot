<?php

declare(strict_types=1);

namespace App\Domain;

class User
{
    private string $id;
    private string $name;
    private ?int $patientId = null;
    private ?string $patientName = null;
    private array $state = [];

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getPatientId(): ?int
    {
        return $this->patientId;
    }

    /**
     * @param int|null $patientId
     */
    public function setPatientId(?int $patientId): void
    {
        $this->patientId = $patientId;
    }

    /**
     * @return string|null
     */
    public function getPatientName(): ?string
    {
        return $this->patientName;
    }

    /**
     * @param string|null $patientName
     */
    public function setPatientName(?string $patientName): void
    {
        $this->patientName = $patientName;
    }

    /**
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * @param array $state
     */
    public function setState(array $state): void
    {
        $this->state = $state;
    }
}
