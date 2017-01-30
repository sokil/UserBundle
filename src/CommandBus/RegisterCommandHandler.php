<?php

namespace Sokil\UserBundle\CommandBus;

use Doctrine\ORM\EntityManagerInterface;
use Sokil\CommandBusBundle\Bus\AbstractCommand;
use Sokil\CommandBusBundle\Bus\AbstractCommandHandler;
use Sokil\UserBundle\Entity\User;

class RegisterCommandHandler extends AbstractCommandHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AbstractCommand $command
     * @return User
     */
    public function handle(AbstractCommand $command)
    {
        $user = new User();
        return $user;
    }
}