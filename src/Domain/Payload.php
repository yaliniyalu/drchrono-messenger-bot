<?php

declare(strict_types=1);

namespace App\Domain;

class Payload implements \JsonSerializable
{
    private string $type;
    private array $data;

    public const LIST_APPOINTMENTS = 'LIST_APPOINTMENTS';
    public const LIST_MEDICATIONS = 'LIST_MEDICATIONS';
    public const LIST_ALLERGIES = 'LIST_ALLERGIES';
    public const LIST_PROBLEMS = 'LIST_PROBLEMS';
    public const LIST_LAB_RESULTS = 'LIST_LAB_RESULTS';
    public const LIST_DOCTORS = 'LIST_DOCTORS';
    public const LIST_OFFICES = 'LIST_OFFICES';

    public const VIEW_MEDICATION = 'VIEW_MEDICATION';
    public const VIEW_LAB_RESULT = 'VIEW_LAB_RESULT';

    public const HEALTH_MENU = 'HEALTH_MENU';
    public const MAIN_MENU = 'MAIN_MENU';

    public const VIEW_APPOINTMENT = 'VIEW_APPOINTMENT';
    public const CANCEL_APPOINTMENT = 'CANCEL_APPOINTMENT';
    public const MAKE_APPOINTMENT = 'MAKE_APPOINTMENT';

    public const VIEW_DOCTOR = 'VIEW_DOCTOR';
    public const VIEW_OFFICE = 'VIEW_OFFICE';

    public const SELECT_DOCTOR = 'SELECT_DOCTOR';
    public const SELECT_OFFICE = 'SELECT_OFFICE';

    public const GET_STARTED = 'PAYLOAD_GET_STARTED';
    public const LOGOUT = 'PAYLOAD_LOGOUT';
    public const HELP = 'PAYLOAD_HELP';

    public static function withData(string $type, array $data = []): string
    {
        return $type . '::' . implode("__", $data);
    }

    public static function withPlaceholder(string $type, array $data = []): string
    {
        return $type . '::' . "{" . implode("}__{", $data) . "}";
    }

    public function __construct(string $type, array $data = [])
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data
        ];
    }

    public static function fromJSON(string $json): self
    {
        $json = json_decode($json, true);
        return new self($json['type'], $json['data']);
    }

    public function __toString()
    {
        return json_encode($this);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
