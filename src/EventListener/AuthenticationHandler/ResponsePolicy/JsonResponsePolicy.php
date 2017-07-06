<?php

namespace Sokil\UserBundle\EventListener\AuthenticationHandler\ResponsePolicy;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\AcceptHeader;

class JsonResponsePolicy
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isAcceptable(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        $acceptHeader = AcceptHeader::fromString($request->headers->get('Accept'));
        if ($acceptHeader->has('application/json')) {
            return true;
        }

        return false;
    }
}
