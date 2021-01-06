<?php

declare(strict_types=1);

namespace App\Domain;

class WitNlp
{
    private array $nlp;

    public const SENTIMENT_POSITIVE = 'positive';
    public const SENTIMENT_NEGATIVE = 'negative';
    public const SENTIMENT_NEURAL = 'neutral';

    public function __construct(array $nlp)
    {
        $this->nlp = $nlp;
    }

    public function getEmail(): ?string
    {
        if (isset($this->nlp['entities']['wit$email:email'], $this->nlp['entities']['wit$email:email'][0])) {
            return $this->nlp['entities']['wit$email:email'][0]['value'];
        }

        return null;
    }

    public function getPhoneNumber(): ?string
    {
        if (
            isset(
                $this->nlp['entities']['wit$phone_number:phone_number'],
                $this->nlp['entities']['wit$phone_number:phone_number'][0]
            )
        ) {
            return $this->nlp['entities']['wit$phone_number:phone_number'][0]['value'];
        }

        return null;
    }

    public function getDateTime(): ?string
    {
        if (
            isset(
                $this->nlp['entities']['wit$datetime:datetime'],
                $this->nlp['entities']['wit$datetime:datetime'][0]
            )
        ) {
            return $this->nlp['entities']['wit$datetime:datetime'][0]['value'];
        }

        return null;
    }

    public function isThanks(): bool
    {
        if (isset($this->nlp['traits']['wit$thanks'], $this->nlp['traits']['wit$thanks'][0])) {
            return $this->nlp['traits']['wit$thanks'][0]['value'] == "true";
        }

        return false;
    }

    public function isBye(): bool
    {
        if (isset($this->nlp['traits']['wit$bye'], $this->nlp['traits']['wit$bye'][0])) {
            return $this->nlp['traits']['wit$bye'][0]['value'] == "true";
        }

        return false;
    }

    public function isGreetings(): bool
    {
        if (isset($this->nlp['traits']['wit$greetings'], $this->nlp['traits']['wit$greetings'][0])) {
            return $this->nlp['traits']['wit$greetings'][0]['value'] == "true";
        }

        return false;
    }

    public function getSentiment(): ?string
    {
        if (isset($this->nlp['traits']['wit$sentiment'], $this->nlp['traits']['wit$sentiment'][0])) {
            return $this->nlp['traits']['wit$sentiment'][0]['value'];
        }

        return null;
    }

    public function getEntityValue(string $name): ?string
    {
        if (isset($this->nlp['entities'][$name], $this->nlp['entities'][$name][0])) {
            return $this->nlp['entities'][$name][0]['value'];
        }

        return null;
    }
}
