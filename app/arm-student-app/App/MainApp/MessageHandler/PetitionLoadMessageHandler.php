<?php

namespace App\MainApp\MessageHandler;

use App\MainApp\Message\PetitionLoadMessage;
use App\MainApp\Service\Admission\PetitionLoadService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\NotifierInterface;


#[AsMessageHandler]
class PetitionLoadMessageHandler
{
    public function __construct(
        private PetitionLoadService $petitionLoadService,
        private NotifierInterface $notifier
    )
    {
    }
    public function __invoke(PetitionLoadMessage $message):void
    {
         $this->petitionLoadService->pubUpdatePetition($message->getAbiturientPetition());
    }
}