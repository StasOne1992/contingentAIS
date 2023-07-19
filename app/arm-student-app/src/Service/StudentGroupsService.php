<?php

namespace App\Service;

use App\Repository\StudentGroupsRepository;
use App\Entity\StudentGroups;
use function PHPUnit\Framework\isNull;

class StudentGroupsService
{
    protected $groupID;

    public function __construct(StudentGroupsRepository $StudentGroupsRepository)
    {
        $this->StudentGroupRepo = $StudentGroupsRepository;
    }

    public function countWoman($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->getSex();
            if ($current->getId() == 2) $count = $count + 1;
        }
        return $count;
    }

    public function countMan($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->getSex();
            if ($current->getId() == 1) $count = $count + 1;
        }
        return $count;
    }

    public function countOrphan($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->isIsOrphan() ?? false;
            if ($current == true) $count = $count + 1;
        }
        return $count;
    }

    public function countInvalid($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->isIsInvalid() ?? false;
            if ($current == true) $count = $count + 1;
        }
        return $count;
    }

    public function countUnderage($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->getBirthData();

            $now=date_create('now');
            $current=$now->diff($current)->y;

            $current=(int)$current;

            if ($current<18 ) $count = $count + 1;
        }
        return $count;
    }
    public function countАdult($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->getBirthData();

            $now=date_create('now');
            $current=$now->diff($current)->y;

            $current=(int)$current;

            if ($current>=18 ) $count = $count + 1;
        }
        return $count;
    }
    public function countWithoutParents($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->isIsWithoutParents() ?? false;
                if ($current == true) $count = $count + 1;

        }
        return $count;
    }
    public function countPoor($groupObj): int
    {
        $group = $groupObj->getStudents()->getValues();
        $count = 0;
        foreach ($group as $student) {
            $current = $student->isIsPoor() ?? false;
            if ($current == true) $count = $count + 1;

        }
        return $count;
    }


    /*public function countWoman($groupObj): int
    {
        $group=$groupObj->getStudents()->getValues();
        $count=0;
        foreach  ($group as $student)
        {
            $sex=$student->getSex();
            if ($sex->getId()==2) $count=$count+1;
        }
        return $count;
    }
*/


    public function generateSocialPasport($groupObj): array
    {
        $socialPassport = [];
        $socialPassport['countStudents'] = count($groupObj->getStudents()->getValues());
        $socialPassport['countWoman'] = $this->countWoman($groupObj);
        $socialPassport['countMan'] = $this->countMan($groupObj);
        $socialPassport['countOrphan'] = $this->countOrphan($groupObj);
        $socialPassport['countInvalid'] = $this->countInvalid($groupObj);
        $socialPassport['countWithoutParents'] = $this->countWithoutParents($groupObj);
        $socialPassport['countIsPoor']=$this->countPoor($groupObj);
        $socialPassport['countUnderage']=$this->countUnderage($groupObj);
        $socialPassport['countАdult']=$this->countАdult($groupObj);
        return $socialPassport;
    }

}