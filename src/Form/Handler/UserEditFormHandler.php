<?php

namespace Sokil\UserBundle\Form\Handler;

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

class UserEditFormHandler
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
     * @var User
     */
    private $user;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ConstraintViolationListInterface
     */
    private $errors;

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

    public function handle(Request $request)
    {
        $id = $request->get('id');

        $isInsert = empty($id);
        $isUpdate = !$isInsert;

        if ($isUpdate) {
            // update
            $this->user = $this->userManager->findUserBy(['id' => $id]);
            if (!$this->user) {
                throw new \RuntimeException('User not found');
            }

            $validationGroups = ['Update'];

            // check permissions
            if (!$this->authorizationChecker->isGranted(UserVoter::PERMISSION_EDIT, $this->user)) {
                throw new AccessDeniedException('Not allowed to update user');
            }

            // remove groups from user
            if ($request->request->has('groups')) {
                $this->user->getGroups()->clear();
            }
        } else {
            // insert
            if (!$this->authorizationChecker->isGranted('ROLE_USER_MANAGER', $this->user)) {
                throw new AccessDeniedException('Not allowed to insert user');
            }

            // insert
            $this->user = $this->userManager->createUser();
            $validationGroups = ['Registration'];

            // set activated
            $this->user->setEnabled(true);
        }

        // set user data
        if ($request->request->has('email')) {
            $this->user->setEmail($request->get('email'));
        }

        if ($request->request->has('name')) {
            $this->user->setName($request->get('name'));
        }

        if ($request->request->has('phone')) {
            $this->user->setPhone($request->get('phone'));
        }

        // set password
        if ($request->request->has('password')) {
            $this->user->setPlainPassword($request->get('password'));
        }

        // set user roles
        if ($request->request->has('roles')) {
            if ($this->authorizationChecker->isGranted(UserVoter::PERMISSION_CHANGE_ROLES, $this->user)) {
                $roles = $request->get('roles');
                if (is_array ($roles)) {
                    $this->user->setRoles($roles);
                }
            }
        }

        // set user groups
        if ($request->request->has('groups')) {
            $groupIdList = $request->get('groups');
            if (is_array($groupIdList)) {
                $this->user->getGroups()->clear();
                foreach ($groupIdList as $groupId) {
                    $this->user->addGroup(
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
                ->getAttributes($this->user);

            // get already set values
            $existedAttributeValues = $this->user->getAttributeValues();

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
                            $this->user->addAttributeValue(new UserAttributeValue(
                                $this->user,
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
                    $this->user->addAttributeValue(new UserAttributeValue(
                        $this->user,
                        $this->entityManager->getReference('UserBundle:UserAttribute', $attributeId),
                        $passedAttributeValues[$attributeId]
                    ));
                }
            }

        }

        // validate user
        $this->errors = $this->validator->validate(
            $this->user,
            null,
            $validationGroups
        );

        if (count($this->errors) > 0) {
            throw new ValidatorException();
        }

        // update user
        $this->userManager->updateUser($this->user);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function getUser()
    {
        return $this->user;
    }
}