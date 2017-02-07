<?php

namespace Sokil\UserBundle\Form\Handler;

use Sokil\UserBundle\CommandBus\ManageUser\AbstractCommandHandler;
use Sokil\UserBundle\CommandBus\UpdateUserCommand;

class UserManagerCommandHandler extends AbstractCommandHandler
{
    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof UpdateUserCommand;
    }
}