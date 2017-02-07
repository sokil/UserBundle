<?php

namespace Sokil\UserBundle\CommandBus;

use Sokil\UserBundle\CommandBus\ManageUser\AbstractCommand;

class UpdateUserCommand extends AbstractCommand
{
    public function getValidationGroups()
    {
        return ['Update'];
    }
}