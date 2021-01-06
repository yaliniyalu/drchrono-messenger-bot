<?php

declare(strict_types=1);

namespace App\Conversation;

use App\Domain\WitNlp;
use App\Service\DrChronoAdminService;
use App\Service\DrChronoPatientService;
use App\Service\Message\MenuService;
use App\Service\UserService;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class OnboardNewUserConversation extends AbstractConversation
{
    protected string $firstName;
    protected string $lastName;
    protected string $gender;
    protected string $dob;
    protected string $email;
    protected string $mobile;

    public function stopsConversation(IncomingMessage $message)
    {
        if (mb_strtolower($message->getText()) == 'cancel') {
            return true;
        }
        return false;
    }

    public function askFirstName()
    {
        $this->ask("What is your first name?", function (Answer $answer) {
            $this->firstName = $answer->getText();
            $this->askLastName();
        });
    }

    public function askLastName()
    {
        $this->ask("What is your last name?", function (Answer $answer) {
            $this->lastName = $answer->getText();
            $this->askGender();
        });
    }

    public function askGender()
    {
        $question = Question::create("What is your gender?")
            ->addButtons([
                Button::create('Male')->value('male'),
                Button::create('Female')->value('female'),
            ]);

        $this->ask(
            $question,
            [
                [
                    'pattern' => 'male|m',
                    'callback' => function () {
                        $this->gender = 'Male';
                        $this->askDateOfBirth();
                    }
                ],
                [
                    'pattern' => 'female|f|fm',
                    'callback' => function () {
                        $this->gender = 'Female';
                        $this->askDateOfBirth();
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

            $this->askEmail();
        });
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

            $this->createPatient();
        });
    }

    protected function createPatient()
    {
        $container = $this->getContainer();

        $this->markSeenAndStartTyping();

        /** @var DrChronoAdminService $admin */
        $admin = $container->get(DrChronoAdminService::class);

        $doctor = $admin->listDoctors()[0]['id'];

        /** @var DrChronoPatientService $service */
        $service = $container->get(DrChronoPatientService::class);

        try {
            $patient = $service->createPatient(
                $this->firstName,
                $this->lastName,
                $this->gender,
                $this->dob,
                $this->email,
                $this->mobile,
                $doctor
            );
        } catch (\Exception $e) {
            $this->say("😟 Unable to Register.\n {$e->getMessage()}");
            $this->bot->startConversation(new OnboardConversation());
            return;
        }

        $user = $this->getUser();

        $user->setPatientId((int) $patient['id']);
        $user->setPatientName($this->firstName . " " . $this->lastName);

        /** @var UserService $service */
        $service = $container->get(UserService::class);
        $service->save($user);

        $this->say("Hello {$user->getName()}");
        $this->say("✅ Registration Successful");
        $this->say("Here is your menu. To get this menu just send me 'menu'.");

        /** @var MenuService $service */
        $service = $container->get(MenuService::class);
        $service->showMenu($this->getUser());
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->askFirstName();
    }
}
