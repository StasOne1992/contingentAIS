<?php

namespace App\Doctrine\Filter;

use App\Entity\Student;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;


class StudentFilter extends SQLFilter
{

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias,): string
    {
        if ($this->hasParameter('userRole') && $this->hasParameter('userGroup')) {
            $userRoles = explode(',', $this->getParameter('userRole'));
            $userGroup = str_replace("'", "", $this->getParameter('userGroup'));
            if ($targetEntity->getReflectionClass()->name == Student::class) {
                if (!(in_array('ROLE_ROOT', $userRoles) || in_array('ROLE_ADMIN', $userRoles))) {
                    $filterString = $targetTableAlias . '.student_group_id in (' . $userGroup . ')';
                    return $filterString;
                } else {
                    return '';
                }
            }
        }
        return '';
    }

}