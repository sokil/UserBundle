<?php

namespace Sokil\UserBundle\CommandBus\RegisterUser;

use Doctrine\ORM\EntityManagerInterface;
use Sokil\CommandBusBundle\CommandBus\CommandHandlerInterface;
use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Sokil\UserBundle\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterUserCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var array
     */
    private $registeredUserRoles;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param array $roles
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        array $roles
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->registeredUserRoles = $roles;
    }

    /**
     * @param RegisterUserCommand $command
     *
     * @return User
     *
     * @throws InvalidCommandException
     */
    public function handle($command)
    {
        $user = new User();
        $user
            ->setEmail($command->getEmail())
            ->setPassword($command->getPassword())
            ->setRoles($this->registeredUserRoles)
            ->setEnabled(true);

        // validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $exception = new InvalidCommandException("User is invalid");
            $exception->setConstraintViolationList($errors);
            throw $exception;
        }

        // persist user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof RegisterUserCommand;
    }
}
