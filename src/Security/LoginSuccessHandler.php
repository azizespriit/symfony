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
return new RedirectResponse($this->router->generate('app_back')); // route name for /back
}

if (in_array('ROLE_USER', $roles)) {
return new RedirectResponse($this->router->generate('app_test')); // route name for /test
}

// fallback
return new RedirectResponse($this->router->generate('app_home'));
}
}
