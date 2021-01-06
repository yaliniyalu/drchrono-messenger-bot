<?php

declare(strict_types=1);

namespace App\Conversation;

use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class OnboardConversation extends AbstractConversation
{
    public function askIsRegistered()
    {
        $this->say('To access this messenger service you must register.');
        $question = Question::create("Are you already registered with {$_ENV['HOSPITAL_NAME']}? 🧐")
            ->addButtons([
                Button::create('Yes')->value('yes'),
                Button::create('No')->value('no'),
            ]);

        $this->ask(
            $question,
            [
                [
                    'pattern' => 'yes|yep|yeah|s',
                    'callback' => function () {
                        $this->bot->startConversation(new OnboardExistingUserConversation());
                    }
                ],
                [
                    'pattern' => 'nah|no|nope|na',
                    'callback' => function () {
                        $this->bot->startConversation(new OnboardNewUserConversation());
                    }
                ],
                [
                    'pattern' => '*',
                    'callback' => function () {
                        $this->repeat();
                    }
                ]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->askIsRegistered();
    }
}
