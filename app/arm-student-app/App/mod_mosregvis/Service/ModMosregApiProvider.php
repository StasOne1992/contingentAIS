<?php

namespace App\mod_mosregvis\Service;

use App\mod_mosregvis\Entity\mosregApiConnection;

class ModMosregApiProvider
{

    private mosregApiConnection $apiConnection;

    public function __construct(mosregApiConnection $apiConnection)
    {
        $this->apiConnection = $apiConnection;
    }

    public function getPetitionList()
    {
        $url = 'http://prof.mo.mosreg.ru/api/spoPetition/search/advancedSearch?page=0&size=5000&order=asc&projection=grid&q={"spoEducationYear":"6"}';
    }

}