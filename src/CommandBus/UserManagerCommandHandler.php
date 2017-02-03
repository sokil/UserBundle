<?php

namespace Sokil\UserBundle\Form\Handler;

use Sokil\CommandBusBundle\Bus\CommandHandlerInterface;
use Sokil\CommandBusBundle\Bus\Exception\InvalidCommandException;
use Sokil\CommandBusBundle\Bus\Exception\CommandUnacceptableByHandlerException;
use Sokil\UserBundle\CommandBus\UserManagerCommand;
use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Entity\UserAttributeValue;
use Sokil\UserBundle\Voter\UserVoter;
use Sokil\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Doctrine\ORM\EntityManagerInterface;

class UserManagerCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;

        // set current user
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            if (is_object($user)) {
                $this->currentUser = $user;
            }
        }

    }

    /**
     * @param object $command
     * @return void
     * @throws CommandUnacceptableByHandlerException
     */
    public function handle($command)
    {
        $id = $request->get('id');

        $isInsert = empty($id);
        $isUpdate = !$isInsert;

        if ($isUpdate) {
            // update
            $user = $this->userManager->findUserBy(['id' => $id]);
            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            $validationGroups = ['Update'];

            // check permissions
            if (!$this->authorizationChecker->isGranted(UserVoter::PERMISSION_EDIT, $user)) {
                throw new AccessDeniedException('Not allowed to update user');
            }

            // remove groups from user
            if ($request->request->has('groups')) {
                $user->getGroups()->clear();
            }
        } else {
            // insert
            if (!$this->authorizationChecker->isGranted('ROLE_USER_MANAGER')) {
                throw new AccessDeniedException('Not allowed to insert user');
            }

            // insert
            $user = $this->userManager->createUser();
            $validationGroups = ['Registration'];

            // set activated
            $user->setEnabled(true);
        }

        // set user data
        if ($request->request->has('email')) {
            $user->setEmail($request->get('email'));
        }

        if ($request->request->has('name')) {
            $user->setName($request->get('name'));
        }

        if ($request->request->has('phone')) {
            $user->setPhone($request->get('phone'));
        }

        // set password
        if ($request->request->has('password')) {
            $user->setPlainPassword($request->get('password'));
        }

        // set user roles
        if ($request->request->has('roles')) {
            if ($this->authorizationChecker->isGranted(UserVoter::PERMISSION_CHANGE_ROLES, $user)) {
                $roles = $request->get('roles');
                if (is_array ($roles)) {
                    $user->setRoles($roles);
                }
            }
        }

        // set user groups
        if ($request->request->has('groups')) {
            $groupIdList = $request->get('groups');
            if (is_array($groupIdList)) {
                $user->getGroups()->clear();
                foreach ($groupIdList as $groupId) {
                    $user->addGroup(
                        $this->entityManager->getReference('UserBundle:Group', $groupId)
                    );
                }
            }
        }

        // set user attributes
        $passedAttributeValues = $request->get('attributeValues');
        if ($isInsert || ($passedAttributeValues && is_array($passedAttributeValues))) {
            // get allowed repository
            $allowedAttributes = $this->entityManager
                ->getRepository('UserBundle:UserAttribute')
                ->getAttributes($user);

            // get already set values
            $existedAttributeValues = $user->getAttributeValues();

            // set passed values
            /* @var UserAttribute $allowedAttribute */
            foreach ($allowedAttributes as $attributeId => $allowedAttribute) {
                // check if attribute allowed
                if (!isset($passedAttributeValues[$attributeId])) {
                    // set default value
                    if ($isInsert) {
                        // get default value
                        $defaultValue = $allowedAttribute->getDefaultValue();
                        if (!$defaultValue && $allowedAttribute->isDefaultValueGetFromCreator()) {
                            $currentUserAttributeValues = $this->currentUser->getAttributeValues();
                            if (isset($currentUserAttributeValues[$attributeId])) {
                                $defaultValue = $currentUserAttributeValues[$attributeId]->getValue();
                            }
                        }
                        // persis default value
                        if ($defaultValue) {
                            $user->addAttributeValue(new UserAttributeValue(
                                $user,
                                $this->entityManager->getReference('UserBundle:UserAttribute', $attributeId),
                                $defaultValue
                            ));
                        }
                    }
                    continue;
                }

                // set attribute value
                if (isset($existedAttributeValues[$attributeId])) {
                    $existedAttributeValues[$attributeId]->setValue($passedAttributeValues[$attributeId]);
                } else {
                    $user->addAttributeValue(new UserAttributeValue(
                        $user,
                        $this->entityManager->getReference('UserBundle:UserAttribute', $attributeId),
                        $passedAttributeValues[$attributeId]
                    ));
                }
            }

        }

        // validate user
        $errors = $this->validator->validate(
            $user,
            null,
            $validationGroups
        );

        if (count($errors) > 0) {
            return [
                'errors' => $errors,
            ];
        }

        // update user
        $this->userManager->updateUser($user);

        return [
            'user' => $user,
        ];
    }

    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof UserManagerCommand;
    }
}