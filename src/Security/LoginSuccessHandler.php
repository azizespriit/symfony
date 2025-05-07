<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
private $router;

public function __construct(RouterInterface $router)
{
$this->router = $router;
}

public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
{
$roles = $token->getRoleNames(); // get roles of the logged-in user

if (in_array('ROLE_ADMIN', $roles)) {
return new RedirectResponse($this->router->generate('app_backA')); // route name for /back
}

if (in_array('ROLE_USER', $roles)) {
    // Get the user ID from the token to pass to the route
    $user = $token->getUser();
    return new RedirectResponse($this->router->generate('app_objectif_front'));
}

// fallback
return new RedirectResponse($this->router->generate('app_home'));
}
}
