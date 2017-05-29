<?php

namespace Sokil\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController extends Controller
{
    /**
     * @Route("/roles", name="roles")
     * @Method({"GET"})
     */
    public function listAction()
    {
        // check access
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        // get roles in hierarchy
        $roleHierarchy = $roles = $this->container->getParameter('security.role_hierarchy.roles');

        // add single roles
        foreach($roleHierarchy as $parentRole => $childRoles) {
            foreach ($childRoles as $childRole) {
                if (empty($roles[$childRole])) {
                    $roles[$childRole] = [];
                }
            }
        }

        return new JsonResponse([
            'roles' => $roles,
        ]);
    }
}