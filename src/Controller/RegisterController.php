<?php

namespace Sokil\UserBundle\Controller;

use Sokil\CommandBusBundle\CommandBus\Exception\InvalidCommandException;
use Sokil\UserBundle\CommandBus\AuthorizeUser\AuthorizeUserCommand;
use Sokil\UserBundle\CommandBus\RegisterUser\RegisterUserCommand;
use Sokil\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @Method({"POST"})
     */
    public function registerAction(Request $request)
    {
        $bus = $this->get('user.command_bus');

        // create user
        $command = new RegisterUserCommand(
            $request->get('email'),
            $request->get('password')
        );

        // register user
        try {
            $user = $bus->handle($command);
        } catch (InvalidCommandException $e) {
            // convert validation errors
            $validationErrors = $this
                ->get('user.validation_errors_converter')
                ->constraintViolationListToArray($e->getConstraintViolationList());

            // validate error response
            return new JsonResponse(
                [
                    'validation' => $validationErrors
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // authorize user
        $bus->handle(new AuthorizeUserCommand($user));

        // send response
        return new JsonResponse([
            'error' => 0,
            'id' => $user->getId(),
            'url' => '/',
        ]);
    }
}
