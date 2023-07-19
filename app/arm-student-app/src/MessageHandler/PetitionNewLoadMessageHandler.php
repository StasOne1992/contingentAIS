<?php

namespace App\MessageHandler;


use App\Message\PetitionNewLoadMessage;
use App\Service\Admission\PetitionLoadService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\NotifierInterface;

#[AsMessageHandler]
class PetitionNewLoadMessageHandler
{
    public function __construct(
        private PetitionLoadService $petitionLoadService,
        private NotifierInterface $notifier
    )
    {
    }

    public function __invoke(PetitionNewLoadMessage $message):void
    {
    	//dd($message);
        $this->petitionLoadService->pubCreatePetition($message->getAbiturientPetition());
    }
}