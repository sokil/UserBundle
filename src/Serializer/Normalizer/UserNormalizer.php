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
    const SERIALIZER_GROUP_USERLIST = 'userlist';

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

    /**
     * @var array
     */
    private $userAttributeNormalizers = [];

    /**
     * UserNormalizer constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerRegistry $entityManagerRegistry
     * @param TranslatorInterface $translator
     */
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

        // serializer groups
        $serializerGroups = empty($context['groups']) || !is_array($context['groups'])
            ? []
            : $context['groups'];

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

        // user list serializer groups
        if (in_array(self::SERIALIZER_GROUP_USERLIST, $serializerGroups)) {
            return $profile;
        }

        // attributes
        $normalizedUserAttributes = $this->normalizeUserAttributes($user);
        if ($normalizedUserAttributes) {
            $profile['attributes'] = $normalizedUserAttributes;
        }

        return $profile;
    }

    /**
     * @param User $user
     * @return array
     */
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
     * @param string $type
     * @return UserAttributeNormalizer
     */
    private function getUserAttributeNormalizer($type)
    {
        if (isset($this->userAttributeNormalizers[$type])) {
            return $this->userAttributeNormalizers[$type];
        }

        $className = self::class . '\\UserAttributeValueNormalizer\\' . ucfirst(strtolower($type)) . 'Normalizer';
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('User attribute normalizer ' . $className . ' is invalid');
        }

        $this->userAttributeNormalizers[$type] = new $className(
            $this->entityManagerRegistry,
            $this->translator
        );

        return $this->userAttributeNormalizers[$type];
    }

    /**
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        if (!$data instanceof User) {
            return false;
        }

        return true;
    }
}