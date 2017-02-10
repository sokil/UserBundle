<?php

namespace Sokil\UserBundle\CommandBus\AuthorizeUser;

use Sokil\CommandBusBundle\CommandBus\CommandHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

class AuthorizeUserCommandHandler implements CommandHandlerInterface
{
    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        AuthenticationManagerInterface $authenticationManager,
        TokenStorageInterface $tokenStorage,
        $firewallName
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->tokenStorage = $tokenStorage;
        $this->firewallName = $firewallName;
    }

    /**
     * @param AuthorizeUserCommand $command
     * @return void
     */
    public function handle($command)
    {
        $user = $command->getUser();

        // create auth token
        $unauthenticatedToken = new UsernamePasswordToken(
            $user,
            null,
            $this->firewallName,
            $user->getRoles()
        );

        // authenticate
        $authenticatedToken = $this
            ->authenticationManager
            ->authenticate($unauthenticatedToken);

        // set token
        $this->tokenStorage->setToken($authenticatedToken);
    }

    /**
     * @param object $command
     * @return bool
     */
    public function supports($command)
    {
        return $command instanceof AuthorizeUserCommand;
    }
}