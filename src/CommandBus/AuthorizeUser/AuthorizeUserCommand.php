<?php

namespace Sokil\UserBundle\CommandBus\AuthorizeUser;

use Sokil\UserBundle\Entity\User;

class AuthorizeUserCommand
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}