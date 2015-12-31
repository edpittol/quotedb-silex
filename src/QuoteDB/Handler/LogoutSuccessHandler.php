<?php

namespace QuoteDB\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\HttpFoundation\Session\Session;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler {
    
    /**
     * 
     * @var Session
     */
    private $session;

    public function __construct(HttpUtils $httpUtils, Session $session, $targetUrl = '/')
    {
       $this->session = $session;
       parent::__construct($httpUtils, $targetUrl);
    }
    
	public function onLogoutSuccess(Request $request) 
	{
	    $this->session->getFlashBag()->add('success', sprintf("Logged out successfully"));
        return parent::onLogoutSuccess($request);
	}
}