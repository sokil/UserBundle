<?php

namespace Sokil\UserBundle\Controller;

use Sokil\UserBundle\CommandBus\RegisterCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction()
    {
        $user = $this
            ->get('sokil.command_bus')
            ->handle(new RegisterCommand());

        return new JsonResponse([
            'error' => 0,
            'id' => $user->getId(),
            'url' => '/',
        ]);
    }
}