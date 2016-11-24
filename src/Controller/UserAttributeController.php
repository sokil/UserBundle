<?php

namespace Sokil\UserBundle\Controller;

use Sokil\UserBundle\Entity\UserAttribute;
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
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        // get list
        $userAttributeList = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:UserAttribute')
            ->findAll();


        return new JsonResponse([
            'attributes' => array_map(
                function(UserAttribute $userAttribute) {
                    return [
                        'id' => $userAttribute->getId(),
                        'name' => $userAttribute->getName(),
                        'type' => $userAttribute->getType(),
                        'printFormat' => $userAttribute->getPrintFormat(),
                        'defaultValue' => $userAttribute->getDefaultValue(),
                        'description' => $userAttribute->getDescription(),
                        'translateable' => $userAttribute->isTranslateable(),
                        'defaultValueGetFromCreator' => $userAttribute->isDefaultValueGetFromCreator(),
                    ];
                },
                $userAttributeList
            ),
        ]);
    }
}