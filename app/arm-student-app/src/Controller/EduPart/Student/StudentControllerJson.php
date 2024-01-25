<?php

namespace App\Controller\EduPart\Student;

use App\Entity\EventsResult;
use App\Entity\Student;
use App\Repository\EventsListRepository;
use App\Repository\StudentGroupsRepository;
use App\Repository\StudentRepository;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class StudentControllerJson
{

}