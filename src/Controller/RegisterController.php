<?php

namespace Sokil\UserBundle\Controller;

use Sokil\UserBundle\CommandBus\RegisterCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @Method({"POST"})
     */
    public function registerAction(Request $request)
    {
        $this
            ->get('sokil.command_bus')
            ->handle(new RegisterCommand(
                $request->get('email'),
                $request->get('password')
            ));

        return new JsonResponse([
            'error' => 0,
            'url' => '/',
        ]);
    }
}