<?php

namespace Sokil\UserBundle\CommandBus;

class CreateUserCommand
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
     * RegisterCommand constructor.
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