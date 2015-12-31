<?php

namespace QuoteDB\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * 
     * @var Session
     */
    private $session;
    
    public function __construct(HttpUtils $httpUtils, Session $session, array $options = array())
    {
        $this->session = $session;
        parent::__construct($httpUtils, $options);
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->session->getFlashBag()->add('success', sprintf('Hello, %s', $token->getUser()->getName()));
        return parent::onAuthenticationSuccess($request, $token);
    }
}