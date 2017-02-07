<?php

namespace Sokil\UserBundle\CommandBus\ManageUser;

class CreateUserCommand extends AbstractCommand
{
    public function getValidationGroups()
    {
        return ['Registration'];
    }
}