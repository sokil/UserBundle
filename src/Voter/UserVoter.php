<?php

namespace Sokil\UserBundle\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter implements VoterInterface
{
    const PERMISSION_VIEW_USER      = 'view';
    const PERMISSION_EDIT           = 'edit';
    const PERMISSION_CHANGE_ROLES   = 'changeRoles';

    /**
     * @var RoleHierarchyVoter;
     */
    private $roleVoter;

    public function __construct($roleVoter)
    {
        $this->roleVoter = $roleVoter;
    }

    public function supportsAttribute($permission)
    {
        return in_array($permission, [
            self::PERMISSION_VIEW_USER,
            self::PERMISSION_EDIT,
            self::PERMISSION_CHANGE_ROLES,
        ]);
    }

    public function supportsClass($class)
    {
        return $class === 'Sokil\UserBundle\Entity\User';
    }

    public function vote(TokenInterface $token, $user, array $permissions)
    {
        if (!$user || !$this->supportsClass(get_class($user))) {
            return self::ACCESS_ABSTAIN;
        }

        // check if admin
        if(VoterInterface::ACCESS_GRANTED === $this->roleVoter->vote($token, $token->getUser(), array('ROLE_ADMIN'))) {
            return self::ACCESS_GRANTED;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($permissions as $permission) {
            if (!$this->supportsAttribute($permission)) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if (call_user_func([$this, 'is' . $permission . 'Granted'] , $user, $token)) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }

    protected function isViewGranted(UserInterface $user, TokenInterface $token = null)
    {
        $currentUser = $token->getUser();
        if (!($currentUser instanceof UserInterface)) {
            return false;
        }

        if ($user->getId() === $currentUser->getId()) {
            return true;
        }

        if(VoterInterface::ACCESS_GRANTED !== $this->roleVoter->vote($token, $currentUser, array('ROLE_USER_VIEWER'))) {
            return false;
        }

        return false;
    }

    /**
     * @param UserInterface $user
     * @param TokenInterface|null $token
     * @return boolIf user can edit himself or other
     */
    protected function isEditGranted(UserInterface $user, TokenInterface $token = null)
    {
        if ($user->getId() === $token->getUser()->getId()) {
            return true;
        }

        if (VoterInterface::ACCESS_GRANTED !== $this->roleVoter->vote($token, $token->getUser(), array('ROLE_USER_MANAGER'))) {
            return false;
        }

        return false;
    }

    /**
     * Permission to change user's roles
     * @param UserInterface $user
     * @param TokenInterface|null $token
     * @return bool
     */
    protected function isChangeRolesGranted(UserInterface $user, TokenInterface $token = null)
    {
        return false;
    }
}