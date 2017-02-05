<?php

namespace Sokil\UserBundle\CommandBus\AuthorizeUser;

use Sokil\CommandBusBundle\Bus\CommandHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthorizeUserCommandHandler implements CommandHandlerInterface
{

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        RequestStack $requestStack,
        $firewallName
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->firewallName = $firewallName;
        $this->requestStack = $requestStack;
    }

    /**
     * @param AuthorizeUserCommand $command
     * @return void
     */
    public function handle($command)
    {
        $user = $command->getUser();

        // do some stuff
        $this->userChecker->checkPostAuth($user);

        // create auth token
        $token = new UsernamePasswordToken(
            $user,
            null,
            $this->firewallName,
            $user->getRoles()
        );

        // do some stuff
        $this->sessionStrategy->onAuthentication(
            $this->requestStack->getCurrentRequest(),
            $token
        );

        // set token
        $this->tokenStorage->setToken($token);

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