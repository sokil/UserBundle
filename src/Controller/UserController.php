<?php

namespace Sokil\UserBundle\Controller;

use Sokil\CommandBusBundle\Bus\Exception\InvalidCommandException;
use Sokil\UserBundle\CommandBus\ManageUser\CreateUserCommand;
use Sokil\UserBundle\CommandBus\ManageUser\UpdateUserCommand;
use Sokil\UserBundle\Entity\User;
use Sokil\UserBundle\Voter\UserVoter;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\Validator\Exception\ValidatorException;

class UserController extends Controller
{
    /**
     * @Route("/users", name="users")
     * @Method({"GET"})
     */
    public function listAction(Request $request)
    {
        /* @var $repository \Doctrine\ORM\EntityRepository */

        // check access
        if (!$this->isGranted('ROLE_USER_VIEWER')) {
           throw $this->createAccessDeniedException();
        }

        // create repository
        $repository = $this->getDoctrine()->getRepository('UserBundle:User');
        $queryBuilder = $repository
            ->createQueryBuilder('u')
            ->where('u.deleted = :deleted')
            ->setParameter(':deleted', 0)
            ->orderBy('u.email', 'DESC');

        // query
        $name = $request->get('name');
        if ($name) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('u.name', $queryBuilder->expr()->literal($name . '%')));
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
        $users = $queryBuilder->getQuery()->getResult();

        // get total count of users
        $paginator = new Paginator($queryBuilder);
        
        // return response
        return new JsonResponse([
            'users' => array_map(function(User $user) {
                return [
                    'id'            => $user->getId(),
                    'email'         => $user->getEmail(),
                    'gravatar'      => $user->getGravatarDefaultUrl(),
                    'permissions'   => [
                        UserVoter::PERMISSION_EDIT => $this->isGranted('edit', $user),
                        UserVoter::PERMISSION_CHANGE_ROLES => $this->isGranted('changeRoles', $user),
                    ]
                ];
            }, $users),
            'usersCount' => $paginator->count(),
        ]);
    }

    /**
     * @Route("/users/{id}", name="get_user", requirements={"id": "\d+"})
     * @Route("/users/new", name="get_default_user")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $id = null)
    {
        /* @var $user User */

        // get user
        if ($id) {
            $user = $this->getDoctrine()
                ->getRepository('UserBundle:User')
                ->find($id);

            if (!$user || $user->isDeleted()) {
                throw new NotFoundHttpException('User not found');
            }
        } else {
            $user = new User();
        }

        // check access
        if (!$this->isGranted('view', $user)) {
            throw $this->createAccessDeniedException();
        }

        $profile = $this->get('user.user_normalizer')->normalize($user);

        return new JsonResponse($profile);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     *
     * @Route("/users/{id}", name="save_user", requirements={"id": "\d+"})
     * @Route("/users", name="save_new_user")
     * @Method({"PUT", "POST", "PATCH"})
     */
    public function saveAction(Request $request, $id = null)
    {
        // check access
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        // create command
        if (empty($id)) {
            // check permission
            if (!$this->isGranted('ROLE_USER_MANAGER')) {
                throw new AccessDeniedHttpException('Not allowed to insert user');
            }
            // create user instance
            $user = new User();
            // create command
            $command = new CreateUserCommand($user);
        } else {
            // find user
            $user = $this->getDoctrine()->getRepository('UserBundle:User')->find($id);
            if (empty($user)) {
                throw new NotFoundHttpException('User not found');
            }
            // check permissions
            if (!$this->isGranted(UserVoter::PERMISSION_EDIT, $user)) {
                throw new AccessDeniedHttpException('Not allowed to update user');
            }
            // create command
            $command = new UpdateUserCommand($user);
        }

        // set user params to handle
        $email = $request->get('email');
        if ($email) {
            $command->setEmail($email);
        }

        $password = $request->get('password');
        if ($password) {
            $command->setPassword($password);
        }

        $name = $request->get('name');
        if ($name) {
            $command->setName($name);
        }

        $roles = $request->get('roles');
        if (is_array($roles)) {
            $command->setRoles($roles);
        }

        $groups = $request->get('groups');
        if (is_array($groups)) {
            $command->setGroups($request->get('groups'));
        }

        $attributeValues = $request->get('attributeValues');
        if ($attributeValues) {
            $command->setAttributeValues($request->get('attributeValues'));
        }

        // handle command
        try {
            $this->get('user.command_bus')->handle($command);
        } catch (InvalidCommandException $e) {
            return new JsonResponse(
                [
                    'validation' => $this
                        ->get('user.validation_errors_converter')
                        ->constraintViolationListToArray($e->getConstraintViolationList())
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        } catch(\Exception $e) {
            return new JsonResponse(
                [
                    'message'       => $e->getMessage(),
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // show success response
        return new JsonResponse([
            'id' => $user->getId(),
        ]);
    }

    /**
     * @Route("/users/{id}", name="delete_user", requirements={"id": "\d+"})
     * @Method({"DELETE"})
     */
    public function deleteAction($id)
    {
        /* @var $user User */

        // check access
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        // get user instance
        $user = $this
            ->getDoctrine()
            ->getRepository('UserBundle:User')
            ->find(['id' => $id]);

        // check permissions
        if (!$this->isGranted(UserVoter::PERMISSION_EDIT, $user)) {
            throw $this->createAccessDeniedException();
        }

        // delete user
        $user->delete();

        // persist changes
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);

        // flush changes
        try {
            $em->flush();
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'message' => $e->getMessage(),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
}
