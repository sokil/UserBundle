<?php

namespace Sokil\UserBundle\Controller;

use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Sokil\UserBundle\CommandBus\ManageUserAttribute\CreateEntityUserAttributeCommand;
use Sokil\UserBundle\CommandBus\ManageUserAttribute\CreateStringUserAttributeCommand;
use Sokil\UserBundle\CommandBus\ManageUserAttribute\UpdateEntityUserAttributeCommand;
use Sokil\UserBundle\CommandBus\ManageUserAttribute\UpdateStringUserAttributeCommand;
use Sokil\UserBundle\Entity\UserAttribute;
use Sokil\UserBundle\Serializer\Normalizer\UserAttributeNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sokil\UserBundle\Entity\UserAttribute\StringAttribute;
use Sokil\UserBundle\Entity\UserAttribute\EntityAttribute;

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
     * @Route("new", name="users_attributes_get_new", requirements={"id": "\d+"})
     * @Route("{id}", name="users_attributes_get", requirements={"id": "\d+"})
     * @Method({"GET"})
     */
    public function getAction($id = null, Request $request)
    {
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        // get attribute
        $normalizedUserAttribute = null;
        if ($id) {
            $userAttributeRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('UserBundle:UserAttribute');

            $userAttribute = $userAttributeRepository->find($id);
            if (empty($userAttribute)) {
                throw new NotFoundHttpException('User attribute not found');
            }
        } else {
            $type = $request->get('type');
            if ($type === 'string') {
                $userAttribute = new StringAttribute();
            } else if ($type === 'entity') {
                $userAttribute = new EntityAttribute();
            } else {
                throw new BadRequestHttpException('Unknown attribute type specified');
            }
        }

        // serialize groups
        $serializeGroups = [];
        if ($request->get('form')) {
            $serializeGroups[] = UserAttributeNormalizer::SERIALIZATION_GROUP_FORM;
        }

        // normalize attribute
        $normalizedUserAttribute = $this
            ->get('user.user_attribute_normalizer')
            ->normalize(
                $userAttribute,
                null,
                [
                    'groups' => $serializeGroups
                ]
            );

        // send json
        return new JsonResponse(array_filter([
            'attribute' => $normalizedUserAttribute,
        ]));
    }

    /**
     * @Route("", name="users_attributes_create")
     * @Method({"PUT", "POST"})
     */
    public function createAction(Request $request)
    {
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        // create command
        $type = $request->get('type');

        // create attribute
        if ($type === 'string') {
            $command = new CreateStringUserAttributeCommand();
            $userAttribute = new StringAttribute();
        } else if ($type === 'entity') {
            $command = new CreateEntityUserAttributeCommand();
            $userAttribute = new EntityAttribute();
        } else {
            throw new BadRequestHttpException('Unknown attribute type specified');
        }

        $command->setUserAttribute($userAttribute);

        // update fields
        $command
            ->setName($request->get('name'))
            ->setDescription($request->get('description'))
            ->setDefaultValue($request->get('defaultValue'));

        // handle creation
        try {
            $this->get('user.command_bus')->handle($command);
        } catch (InvalidCommandException $e) {
            return new JsonResponse([
                'validation' => $this
                    ->get('user.validation_errors_converter')
                    ->constraintViolationListToArray($e->getConstraintViolationList()),
            ], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errorMessage' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([
            'id' => $userAttribute->getId(),
        ]);
    }

    /**
     * @Route("{id}", name="users_attributes_update", requirements={"id": "\d+"})
     * @Method({"PUT", "POST"})
     */
    public function updateAction(Request $request, $id)
    {
        // check access
        if (!$this->isGranted('ROLE_USER_MANAGER')) {
            throw $this->createAccessDeniedException();
        }

        // get attribute
        $userAttributeRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:UserAttribute');

        $userAttribute = $userAttributeRepository->find($id);
        if (empty($userAttribute)) {
            throw new NotFoundHttpException('User attribute not found');
        }

        // create command
        if ($userAttribute instanceof StringAttribute) {
            $command = new UpdateStringUserAttributeCommand();
        } else if ($userAttribute instanceof EntityAttribute) {
            $command = new UpdateEntityUserAttributeCommand();
        } else {
            throw new BadRequestHttpException('Unknown attribute type specified');
        }

        $command->setUserAttribute($userAttribute);

        // update fields
        $command
            ->setName($request->get('name'))
            ->setDescription($request->get('description'))
            ->setDefaultValue($request->get('defaultValue'));

        // handle update
        try {
            $this->get('user.command_bus')->handle($command);
        } catch (InvalidCommandException $e) {
            return new JsonResponse([
                'validation' => $this
                    ->get('user.validation_errors_converter')
                    ->constraintViolationListToArray($e->getConstraintViolationList()),
            ], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errorMessage' => $e->getMessage(),
            ]);
        }

        return new JsonResponse([
            'id' => $userAttribute->getId(),
        ]);
    }
}