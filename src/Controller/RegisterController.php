<?php

namespace Sokil\UserBundle\Controller;

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
        return new JsonResponse([
            'error' => 0,
            'url' => '/',
        ]);
    }
}