<?php

namespace App\Doctrine\Filter;

use App\Entity\StudentGroups;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class studentGroupFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($this->hasParameter('userRole') && $this->hasParameter('userGroup')) {
            $userRoles = explode(',', $this->getParameter('userRole'));
            $userGroup = str_replace("'", "", $this->getParameter('userGroup'));

            if ($targetEntity->getReflectionClass()->name == StudentGroups::class) {

                if (!(in_array('ROLE_ROOT', $userRoles) || in_array('ROLE_ADMIN', $userRoles))) {
                    $filterString = $targetTableAlias . '. id in (' . $userGroup . ')';
                    return $filterString;
                } else {
                    return '';
                }
            }
        }
        return '';
    }
}