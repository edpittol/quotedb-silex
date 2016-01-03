<?php

namespace QuoteDB\Handler;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Silex\Application;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler {
    
    /**
     * 
     * @var Session
     */
    private $session;
    
    private $log;

    public function __construct(HttpUtils $httpUtils, Application $app, $targetUrl = '/')
    {
       $this->app = $app;
       parent::__construct($httpUtils, $targetUrl);
    }
    
	public function onLogoutSuccess(Request $request) 
	{
	    $this->app['monolog']->addInfo(sprintf("User '%s' logged out.", $this->app['user']->getEmail()));
	    $this->app['session']->getFlashBag()->add('success', "Logged out successfully");
        return parent::onLogoutSuccess($request);
	}
}