<?php

namespace Sokil\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserAttributeController extends Controller
{
    /**
     * @Route("/users/attributes", name="users_attributes_list")
     * @Method({"GET"})
     */
    public function listAction(Request $request)
    {
        return new JsonResponse([]);
    }
}