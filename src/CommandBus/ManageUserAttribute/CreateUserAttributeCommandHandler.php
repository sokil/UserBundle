<?php

namespace Sokil\UserBundle\CommandBus\ManageUserAttribute;

use Doctrine\ORM\EntityManagerInterface;
use Sokil\CommandBusBundle\CommandBus\CommandHandlerInterface;
use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserAttributeCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param AbstractCreateCommand $command
     * @throws InvalidCommandException
     */
    public function handle($command)
    {
        // get user attribute
        $userAttribute = $command->getUserAttribute();

        // set attribute parameters
        $userAttribute
            ->setName($command->getName())
            ->setDescription($command->getDescription())
            ->setDefaultValue($command->getDefaultValue());

        // validate attribute
        $errors = $this->validator->validate($userAttribute);
        if (count($errors) > 0) {
            $exception = new InvalidCommandException();
            $exception->setConstraintViolationList($errors);
            throw new $exception;
        }

        // persist
        $this->entityManager->persist($userAttribute);
        $this->entityManager->flush();
    }

    public function supports($command)
    {
        return $command instanceof AbstractCreateCommand;
    }
}