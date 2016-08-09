<?php

namespace Sokil\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Sokil\UserBundle\Entity\User;

class UserAttributeRepository extends EntityRepository
{
    /**
     * Get list of user's attributes
     *
     * @param User $user
     * @return \Doctrine\ORM\AbstractQuery
     */
    private function createAttributesQuery(User $user)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT ua
                FROM UserBundle:UserAttribute ua
                LEFT JOIN ua.groups ug
                INDEX BY ua.id
                WHERE ug.id = 1
            ');

        return $query;
    }

    /**
     * Get attributes as array
     * @return array
     */
    public function getAttributesArray(User $user)
    {
        return $this->createAttributesQuery($user)->getArrayResult();
    }

    /**
     * Get attributes as array
     * @return array
     */
    public function getAttributes(User $user)
    {
        return $this->createAttributesQuery($user)->getResult();
    }
}