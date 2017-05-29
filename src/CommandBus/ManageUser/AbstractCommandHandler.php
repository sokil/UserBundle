<?php

namespace Sokil\UserBundle\CommandBus\ManageUser;

use Sokil\CommandBusBundle\CommandBus\CommandHandlerInterface;
use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Entity\UserAttributeValue;
use Sokil\UserBundle\Voter\UserVoter;
use Sokil\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @param EntityManagerInterface $entityManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param ValidatorInterface $validator
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->encoderFactory = $encoderFactory;

    }

    /**
     * Get authorized user
     * @return User|null
     */
    protected function getCurrentUser()
    {
        // set current user
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (!is_object($user)) {
            return null;
        }

        return $user;
    }

    /**
     * @param AbstractCommand $command
     * @return void
     * @throws InvalidCommandException
     */
    public function handle($command)
    {
        // get user instance
        $user = $command->getUser();

        // before modify
        $this->onBeforeModifyUser($user);

        // set user parameters
        $email = $command->getEmail();
        if ($email) {
            $user->setEmail($email);
        }

        $plainPassword = $command->getPassword();
        if ($plainPassword) {
            $this->setEncodePassword($user, $plainPassword);
        }

        $name = $command->getName();
        if ($name) {
            $user->setName($name);
        }

        // set user roles
        $roles = $command->getRoles();
        if ($roles) {
            if ($this->authorizationChecker->isGranted(UserVoter::PERMISSION_CHANGE_ROLES, $user)) {
                $user->setRoles($roles);
            }
        }

        // set user groups
        $groupIdList = $command->getGroups();
        if (is_array($groupIdList)) {
            // remove groups from user
            $user->getGroups()->clear();
            foreach ($groupIdList as $groupId) {
                $user->addGroup(
                    $this->entityManager->getReference('UserBundle:Group', $groupId)
                );
            }
        }

        // set user attributes
        $this->setUserAttributes(
            $user,
            $command->getAttributeValues()
        );

        // validate user
        $errors = $this->validator->validate(
            $user,
            null,
            $command->getValidationGroups()
        );

        if (count($errors) > 0) {
            $exception = new InvalidCommandException();
            $exception->setConstraintViolationList($errors);
            throw $exception;
        }

        // update user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Change user before applying common modifications
     *
     * @param User $user
     */
    protected function onBeforeModifyUser(User $user)
    {
        // override in child classes
    }

    private function setUserAttributes(User $user, array $passedUserAttributeValues)
    {
        // get allowed repository
        $attributeDictionary = $this->entityManager
            ->getRepository('UserBundle:UserAttribute')
            ->getAttributes($user);

        // get already set values
        $actualUserAttributeValues = $user->getAttributeValues();

        // set passed values
        /* @var UserAttribute $attribute */
        foreach ($attributeDictionary as $attributeId => $attribute) {
            // check if attribute allowed
            if (!isset($passedUserAttributeValues[$attributeId])) {
                $this->onEmptyPassedUserAttributeValue($attributeId, $attribute, $user);
                continue;
            }

            // set attribute value
            if (isset($actualUserAttributeValues[$attributeId])) {
                $actualUserAttributeValues[$attributeId]->setValue($passedUserAttributeValues[$attributeId]);
            } else {
                $user->addAttributeValue(
                    new UserAttributeValue(
                        $user,
                        $this->entityManager->getReference('UserBundle:UserAttribute', $attributeId),
                        $passedUserAttributeValues[$attributeId]
                    )
                );
            }
        }
    }

    /**
     * If attribute present in dictionary, but user has not set attribute's value yet
     *
     * @param int $attributeId
     * @param UserAttribute $attribute
     * @param User $user
     */
    protected function onEmptyPassedUserAttributeValue(
        $attributeId,
        UserAttribute $attribute,
        User $user
    ) {
        // override in child classes
    }

    /**
     * Encode plain password
     * @param User $user
     * @param string $plainPassword
     */
    private function setEncodePassword(User $user, $plainPassword)
    {
        $password = $this->encoderFactory->getEncoder($user)->encodePassword(
            $plainPassword,
            $user->getSalt()
        );

        $user->setPassword($password);
    }
}