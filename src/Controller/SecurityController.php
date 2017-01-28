<?php

namespace Sokil\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @Route("/login_check", name="user_security_login")
     */
    public function loginAction()
    {
        throw new \RuntimeException('Request to controller must me captured by the firewall');
    }

    /**
     * @Route("/logout", name="user_security_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('Request to controller must me captured by the firewall');
    }
}