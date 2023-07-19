<?php

namespace App\Service\Admission;

use App\Service\Admission\PetitionLoadService;


class PetitionAsyncService
{
    private array $message;


    public function __construct(
        private PetitionLoadService $petitionLoadService
    )
    {
    }

}