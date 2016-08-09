<?php

namespace Sokil\UserBundle\Controller;

use Sokil\UserBundle\Entity\Group;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/roles/groups")
 */
class RoleGroupController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("", name="role_group_list")
     * @Method({"GET"})
     */
    public function listAction(Request $request)
    {
        /* @var $repository \Doctrine\ORM\EntityRepository */

        // check access
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // create repository
        $repository = $this->getDoctrine()->getRepository('UserBundle:Group');
        $queryBuilder = $repository
            ->createQueryBuilder('ug')
            ->orderBy('ug.name', 'DESC');

        // query
        $name = $request->get('name');
        if ($name) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->like('ug.name', $queryBuilder->expr()->literal($name . '%'))
            );
        }

        // pager
        $limit = (int) $request->get('limit', 20);
        if($limit > 100) {
            $limit = 100;
        }
        $queryBuilder->setMaxResults($limit);

        $offset = (int) $request->get('offset', 0);
        $queryBuilder->setFirstResult($offset);

        // get list of users
        $groups = $queryBuilder->getQuery()->getResult();

        // get total count of users
        $paginator = new Paginator($queryBuilder);

        // return response
        return new JsonResponse([
            'groups' => array_map(function(Group $group) {
                return [
                    'id'    => $group->getId(),
                    'name'  => $group->getName(),
                    'roles' => $group->getRoles(),
                ];
            }, $groups),
            'groupsCount' => $paginator->count(),
        ]);
    }

    /**
     * @Route("/{id}", name="role_group_get", requirements={"id": "\d+"})
     * @Method({"GET"})
     */
    public function getAction($id)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // get entity
        $group = $this->getDoctrine()
            ->getRepository('UserBundle:Group')
            ->find($id);

        if (!$group) {
            throw new NotFoundHttpException("Group not found");
        }

        return new JsonResponse([
            'id' => $group->getId(),
            'name' => $group->getName(),
            'roles' => $group->getRoles(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("", name="role_group_create")
     * @Route("/{id}", name="role_group_update", requirements={"id": "\d+"})
     * @Method({"POST", "PUT"})
     */
    public function saveAction(Request $request, $id = null)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // get entity
        if ($id) {
            $group = $this->getDoctrine()
                ->getRepository('UserBundle:Group')
                ->find($id);

            if (!$group) {
                throw new NotFoundHttpException("Group not found");
            }

            // set data
            $group
                ->setName($request->get('name'))
                ->setRoles($request->get('roles'));
        } else {
            $group = new Group(
                $request->get('name'),
                $request->get('roles')
            );
        }


        // validate entity
        $errors = $this->get('validator')->validate($group);
        if (count($errors) > 0) {
            return new JsonResponse([
                'validation' => $this
                    ->get('user.validation_errors_converter')
                    ->constraintViolationListToArray($errors),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // persist
        $em = $this->getDoctrine()->getManager();
        $em->persist($group);

        // flush
        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'id' => $group->getId(),
        ]);
    }
}