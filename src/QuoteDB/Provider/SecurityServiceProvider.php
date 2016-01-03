<?php

namespace QuoteDB\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $app['security.set_token'] = $app->share(function($app) {
            return new UsernamePasswordToken($app['user'], $app['user']->getPassword(), 'user_firewall', $app['user']->getRoles());
        });
    }

    public function boot(Application $app)
    {
    }
}