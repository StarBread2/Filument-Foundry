<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?RedirectResponse
    {
        $path = $request->getPathInfo();

        // If user is Admin or Worker → they CAN view the dashboard → redirect there
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_WORKER')) 
        {
            return new RedirectResponse('/management/dashboard');
        }

        // Otherwise, redirect to home
        return new RedirectResponse('/home');
    }
}
