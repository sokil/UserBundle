<?php

namespace Sokil\UserBundle\CommandBus;

use Doctrine\ORM\EntityManagerInterface;
use Sokil\CommandBusBundle\Bus\CommandHandlerInterface;
use Sokil\CommandBusBundle\Bus\Exception\InvalidCommandException;
use Sokil\CommandBusBundle\Bus\Exception\CommandUnacceptableByHandlerException;
use Sokil\UserBundle\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RegisterCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
    }

    /**
     * @param object $command
     *
     * @return void
     *
     * @throws CommandUnacceptableByHandlerException
     */
    public function handle($command)
    {
        $user = new User();
        $user
            ->setEmail($command->getEmail())
            ->setPlainPassword($command->getPassword());
    }

    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof RegisterCommand;
    }
}