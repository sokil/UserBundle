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

        // repository
        $userAttributeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:UserAttribute');

        // get list
        $userAttributeList = $userAttributeRepository->findAll();

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
            'availableTypes' => $this
                ->get('user.converter.entity_discriminator_map')
                ->getDiscriminatorMap(UserAttribute::class),
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

        $userAttributeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:UserAttribute');

        // get attribute
        if (empty($id)) {
            $normalizedUserAttribute = [];
        } else {
            // get attribute
            $userAttribute = $userAttributeRepository->find($id);
            if (empty($userAttribute)) {
                throw new NotFoundHttpException('User attribute not found');
            }

            // normalize attribute
            $normalizedUserAttribute = $this
                ->get('user.user_attribute_normalizer')
                ->normalize($userAttribute);
        }

        // send json
        return new JsonResponse([
            'attribute' => $normalizedUserAttribute,
            'availableTypes' => $this
                ->get('user.converter.entity_discriminator_map')
                ->getDiscriminatorMap(UserAttribute::class),
        ]);
    }

    /**
     * @Route("{id}", name="users_attributes_save", requirements={"id": "\d+"})
     * @Method({"PUT", "POST"})
     */
    public function saveAction(Request $request, $id)
    {
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        $userAttributeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:UserAttribute');

        // get attribute
        $userAttribute = $userAttributeRepository->find($id);
        if (empty($userAttribute)) {
            throw new NotFoundHttpException('User attribute not found');
        }

        // update fields
        $userAttribute
            ->setName($request->get('name'))
            ->setDescription($request->get('description'))
            ->setDefaultValue($request->get('defaultValue'))
            ->setPrintFormat($request->get('printFormat'));

        // validate attribute
        $errors = $this->get('validator')->validate($userAttribute);
        if (count($errors) > 0) {
            return new JsonResponse([
                'validation' => $this
                    ->get('user.validation_errors_converter')
                    ->constraintViolationListToArray($errors),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // persist
        $em = $this->getDoctrine()->getManager();
        $em->persist($userAttribute);

        // flush
        try {
            $em->flush();
        } catch (\Exception $e) {
            // send json
            return new JsonResponse([
                'errorMessage' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([]);
    }
}