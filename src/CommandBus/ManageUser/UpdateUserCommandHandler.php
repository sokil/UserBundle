<?php

namespace Sokil\UserBundle\CommandBus\ManageUser;

class UpdateUserCommandHandler extends AbstractCommandHandler
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