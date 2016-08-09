<?php

namespace Sokil\UserBundle\Serializer\Normalizer;

use Sokil\UserBundle\Entity\UserAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Sokil\UserBundle\Entity\User;
use Sokil\UserBundle\Voter\UserVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as EntityManagerRegistry;
use Symfony\Component\Translation\TranslatorInterface;

class UserNormalizer implements NormalizerInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EntityManagerRegistry
     */

    private $entityManagerRegistry;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    private $userAttributeNormalizers = [];

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerRegistry $entityManagerRegistry,
        TranslatorInterface $translator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManagerRegistry = $entityManagerRegistry;
        $this->translator = $translator;
    }

    public function normalize($user, $format = null, array $context = array())
    {
        if (!($user instanceof User)) {
            throw new \InvalidArgumentException('User must be instance of ' . User::class);
        }

        // roles
        $permissions = [
            UserVoter::PERMISSION_EDIT => $this->authorizationChecker->isGranted('edit', $user),
            UserVoter::PERMISSION_CHANGE_ROLES => $this->authorizationChecker->isGranted('changeRoles', $user),
        ];

        if (!$user->getId()) {
            return [
                'permissions' => $permissions,
            ];
        }

        // profile
        $profile = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'gravatar' => $user->getGravatarDefaultUrl(),
            'roles' => $user->getRoles(),
            'ownRoles' => $user->getOwnRoles(),
            'groups' => $user->getGroups()
                ->map(function($group) {
                    return [
                        'id' => $group->getId(),
                        'name' => $group->getName(),
                    ];
                })
                ->toArray(),
            'permissions'  => $permissions,
        ];

        // attributes
        $normalizedUserAttributes = $this->normalizeUserAttributes($user);
        if ($normalizedUserAttributes) {
            $profile['attributes'] = $normalizedUserAttributes;
        }

        return $profile;
    }

    private function normalizeUserAttributes(User $user)
    {
        $normalizedUserAttributes = [];

        // allowed user's attributes
        $availableUserAttributes = $this->entityManagerRegistry
            ->getRepository('UserBundle:UserAttribute')
            ->getAttributes($user);

        if (!$availableUserAttributes) {
            return [];
        }

        // user values
        $attributeValues = $user->getAttributeValues();

        // normalize attribute

        /* @var UserAttribute $availableUserAttribute */
        foreach ($availableUserAttributes as $attributeId => $availableUserAttribute) {
            // prepare value
            if (isset($attributeValues[$attributeId])) {
                $userAttributeValue = $attributeValues[$attributeId];
            } else {
                $userAttributeValue = null;
            }

            // check view permission
            $viewRoles = $availableUserAttribute->getViewRoles();
            if ($viewRoles && !$this->authorizationChecker->isGranted($viewRoles)) {
                continue;
            }

            // normalize
            $normalizedAttribute = $this
                ->getUserAttributeNormalizer($availableUserAttribute->getType())
                ->normalize(
                    $availableUserAttribute,
                    $userAttributeValue
                );

            // permissions
            $editRoles = $availableUserAttribute->getEditRoles();
            $normalizedAttribute['permissions'] = [
                'edit' => !$editRoles || $this->authorizationChecker->isGranted($editRoles),
            ];

            $normalizedUserAttributes[$attributeId] = $normalizedAttribute;
        }

        return $normalizedUserAttributes;
    }

    /**
     * @param $type UserAttributeNormalizer
     */
    private function getUserAttributeNormalizer($type)
    {
        if (isset($this->userAttributeNormalizers[$type])) {
            return $this->userAttributeNormalizers[$type];
        }

        $className = self::class . '\\UserAttributeNormalizer\\' . ucfirst(strtolower($type)) . 'Normalizer';
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('User attribute normalizer ' . $className . ' is invalid');
        }

        $this->userAttributeNormalizers[$type] = new $className(
            $this->entityManagerRegistry,
            $this->translator
        );

        return $this->userAttributeNormalizers[$type];
    }

    public function supportsNormalization($data, $format = null)
    {
        return true;
    }
}