<?php

namespace Sokil\UserBundle\CommandBus\RegisterUser;

use Sokil\UserBundle\Entity\User;

class RegisterUserCommand
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $email
     * @param string $password
     */
    public function __construct(
        $email,
        $password
    ) {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
