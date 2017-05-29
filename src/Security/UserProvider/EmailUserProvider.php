<?php

namespace Sokil\UserBundle\Security\UserProvider;

use Sokil\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

class EmailUserProvider implements UserProviderInterface
{
    private $userRepository;

    public function __construct(EntityRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $email The user's email
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($email)
    {
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UsernameNotFoundException('Email not found');
        }

        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (!$user) {
            throw new UsernameNotFoundException('User with passed email not found');
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'Expected an instance of %s, but got "%s".',
                User::class,
                get_class($user)
            ));
        }

        $user = $this->userRepository->find($user->getId());
        if (!$user) {
            throw new UsernameNotFoundException('User not found');
        }

        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}