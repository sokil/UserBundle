<?php

namespace Sokil\UserBundle\Controller;

use Sokil\UserBundle\Entity\UserAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/users/attributes/")
 */
class UserAttributeController extends Controller
{
    /**
     * @Route("", name="users_attributes_list")
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

        // available types
        $userAttributeClassMetadata = $this
            ->getDoctrine()
            ->getManager()
            ->getClassMetadata(UserAttribute::class);

        // return json
        return new JsonResponse([
            'attributes' => array_map(
                function(UserAttribute $userAttribute) {
                    return $this
                        ->get('user.user_attribute_normalizer')
                        ->normalize($userAttribute);
                },
                $userAttributeList
            ),
            'availableTypes' => array_keys($userAttributeClassMetadata->discriminatorMap),
        ]);
    }

    /**
     * @Route("{id}", name="users_attributes_get", requirements={"id": "\d+"})
     * @Route("new", name="users_attributes_get_new")
     * @Method({"GET"})
     */
    public function getAction($id = null)
    {
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        // get attribute
        if (empty($id)) {
            $normalizedUserAttribute = [];
        } else {
            // get attribute
            $userAttribute = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('UserBundle:UserAttribute')
                ->find($id);

            if (empty($userAttribute)) {
                throw new NotFoundHttpException('User attribute not found');
            }

            // normalize attribute
            $normalizedUserAttribute = $this
                ->get('user.user_attribute_normalizer')
                ->normalize($userAttribute);
        }

        // available types
        $userAttributeClassMetadata = $this
            ->getDoctrine()
            ->getManager()
            ->getClassMetadata(UserAttribute::class);

        // send json
        return new JsonResponse([
            'attribute' => $normalizedUserAttribute,
            'availableTypes' => array_keys($userAttributeClassMetadata->discriminatorMap),
        ]);
    }
}