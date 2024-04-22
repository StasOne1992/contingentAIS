<?php

namespace App\MainApp\Service\Admission;


class PetitionAsyncService
{
    private array $message;


    public function __construct(
        private PetitionLoadService $petitionLoadService
    )
    {
    }

}