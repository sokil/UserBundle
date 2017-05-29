<?php

namespace Sokil\UserBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        throw new \Exception("Command not implemented");
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create user')
            ->setHelp('Create user');
    }
}
