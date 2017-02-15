<?php

namespace Sokil\UserBundle\CommandBus\ManageUserAttribute;

use Doctrine\ORM\EntityManagerInterface;
use Sokil\CommandBusBundle\CommandBus\CommandHandlerInterface;
use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Sokil\UserBundle\Entity\UserAttribute\EntityAttribute;
use Sokil\UserBundle\Entity\UserAttribute\StringAttribute;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUserAttributeCommandHandler implements CommandHandlerInterface
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
     * @param AbstractUpdateCommand $command
     * @throws InvalidCommandException
     */
    public function handle($command)
    {
        // create attribute
        if ($command instanceof UpdateStringUserAttributeCommand) {
            $userAttribute = new StringAttribute();
        } elseif ($command instanceof UpdateEntityUserAttributeCommand) {
            $userAttribute = new EntityAttribute();
        } else {
            throw new InvalidCommandException('Unknown attribute type specified');
        }

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
        return $command instanceof AbstractUpdateCommand;
    }
}