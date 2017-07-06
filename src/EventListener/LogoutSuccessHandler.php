<?php

namespace Sokil\UserBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Sokil\UserBundle\EventListener\ResponsePolicy\JsonResponsePolicy;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        $jsonPolicy = new JsonResponsePolicy();

        if ($jsonPolicy->isAcceptable($request)) {
            return new JsonResponse([
                'error' => 0,
                'url' => $this->targetUrl,
            ]);
        } else {
            return $this->httpUtils->createRedirectResponse($request, $this->targetUrl);
        }
    }
}
