<?php

namespace Sokil\UserBundle\CommandBus\ManageUser;

use Sokil\UserBundle\CommandBus\ManageUser\AbstractCommand;

class UpdateUserCommand extends AbstractCommand
{
    public function getValidationGroups()
    {
        return ['Update'];
    }
}