<?php

namespace QuoteDB\Handler;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Session\Session;
use Silex\Application;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * 
     * @var Application
     */
    private $app;
    
    public function __construct(HttpUtils $httpUtils, Application $app, array $options = array())
    {
        $this->app = $app;
        parent::__construct($httpUtils, $options);
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
	    $this->app['monolog']->addInfo(sprintf("User '%s' logged in.", $this->app['user']->getEmail()));
        $this->app['session']->getFlashBag()->add('success', sprintf('Hello, %s', $token->getUser()->getName()));
        return parent::onAuthenticationSuccess($request, $token);
    }
}