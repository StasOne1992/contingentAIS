<?php

namespace App\Message;

class PetitionLoadMessage
{
    public function __construct(
        private array $abiturientPetition,
    )

    {
    }

    public
    function getAbiturientPetition(): array
    {
        return $this->abiturientPetition;
    }
}