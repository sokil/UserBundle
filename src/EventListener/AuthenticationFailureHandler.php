<?php

namespace Sokil\UserBundle\EventListener;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sokil\UserBundle\EventListener\ResponsePolicy\JsonResponsePolicy;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $jsonPolicy = new JsonResponsePolicy();

        if ($jsonPolicy->isAcceptable($request)) {
            return new JsonResponse([
                'error' => 1,
                'message' => $exception->getMessage(),
            ]);
        } else {
            return parent::onAuthenticationFailure($request, $exception);
        }
    }
}
