<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Domain\WitNlp;
use App\Service\DrChronoPatientService;
use App\Service\Message\MenuService;
use App\Service\UserService;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class OnboardExistingUserConversation extends AbstractConversation
{
    public string $email;
    public string $mobile;
    public string $dob;

    public function stopsConversation(IncomingMessage $message)
    {
        if (mb_strtolower($message->getText()) == 'cancel') {
            return true;
        }
        return false;
    }

    public function askEmail()
    {
        $this->ask("What is your email address?", function (Answer $answer) {
            $nlp = $this->bot->getMessage()->getExtras('nlp');
            $nlp = new WitNlp($nlp);

            $email = $nlp->getEmail();
            if (!$email) {
                $this->say("You have provided invalid email address");
                $this->repeat();
                return;
            }
            $this->email = $email;

            $this->askMobile();
        });
    }

    public function askMobile()
    {
        $this->ask("What is your mobile number?", function (Answer $answer) {
            $nlp = $this->bot->getMessage()->getExtras('nlp');

            $mobile = (new WitNlp($nlp))->getPhoneNumber();
            if (!$mobile) {
                $this->say("You have provided invalid mobile number");
                $this->repeat();
                return;
            }

            $mobile = preg_replace('/[^0-9]/', '', $mobile);
            $mobile_parts = str_split($mobile, 3);

            if (count($mobile_parts) < 2) {
                $this->say("You have provided invalid mobile number");
                $this->repeat();
                return;
            }

            $mobile_parts[0] = "({$mobile_parts[0]}) ";
            $mobile_parts[1] = $mobile_parts[1] . "-";

            $this->mobile = implode($mobile_parts);

            $this->askDateOfBirth();
        });
    }

    public function askDateOfBirth()
    {
        $this->ask("What is your date of birth?", function (Answer $answer) {
            $nlp = $this->bot->getMessage()->getExtras('nlp');

            $dob = (new WitNlp($nlp))->getDateTime();
            if (!$dob) {
                $dob = strtotime($answer->getText());
                if ($dob) {
                    $this->dob = date('Y-m-d', $dob);
                } else {
                    $this->say("You have provided invalid date");
                    $this->repeat();
                    return;
                }
            } else {
                $this->dob = explode('T', $dob)[0];
            }

            $this->loginPatient();
        });
    }

    private function loginPatient()
    {
        $this->markSeenAndStartTyping();

        $container = $this->getContainer();

        /** @var DrChronoPatientService $service */
        $service = $container->get(DrChronoPatientService::class);
        $patient = $service->findPatient($this->email, $this->dob, $this->mobile);

        if (!$patient) {
            $this->say("I couldn't find any patient with provided data. Please try again. 🧐");
            $this->bot->startConversation(new OnboardConversation());
            return;
        }

        $name = $patient['first_name'] . ' ' . $patient['last_name'];

        $user = $this->getUser();

        $user->setPatientId((int) $patient['id']);
        $user->setPatientName($name);

        /** @var UserService $service */
        $service = $container->get(UserService::class);
        $service->save($user);

        $this->say("Hello {$name}");
        $this->say("Here is your menu. To get this menu just send me 'menu'.");

        /** @var MenuService $service */
        $service = $container->get(MenuService::class);
        $service->showMenu($user);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->askEmail();
    }
}
