<?php

namespace Sokil\UserBundle\EventListener;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sokil\UserBundle\EventListener\ResponsePolicy\JsonResponsePolicy;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $jsonPolicy = new JsonResponsePolicy();

        if ($jsonPolicy->isAcceptable($request)) {
            return new JsonResponse([
                'error' => 0,
                'url' => $this->determineTargetUrl($request),
            ]);
        } else {
            return parent::onAuthenticationSuccess($request, $token);
        }
    }
}
