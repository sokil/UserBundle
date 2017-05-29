<?php

namespace Sokil\UserBundle\CommandBus\ManageUserAttribute;

use Sokil\UserBundle\Entity\UserAttribute;

abstract class AbstractCreateCommand extends AbstractCommand
{
    /**
     * @var UserAttribute
     */
    private $userAttribute;

    /**
     * @return UserAttribute
     */
    public function getUserAttribute()
    {
        return $this->userAttribute;
    }

    /**
     * @param UserAttribute $userAttribute
     */
    public function setUserAttribute($userAttribute)
    {
        $this->userAttribute = $userAttribute;
    }
}