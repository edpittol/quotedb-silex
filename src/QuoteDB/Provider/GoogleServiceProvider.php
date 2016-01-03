<?php

namespace QuoteDB\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class GoogleServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $app['google.config'] = $app->share(function() use ($app) {
            return array(
                'cliend_id' => (string) $app['config']['social']['google']['client_id'],
                'client_secret' => (string) $app['config']['social']['google']['client_secret'],
            );
        });
        
        $app['google'] = $app->share(function() use ($app) {
            $client = new \Google_Client();
            $client->setClientId($app['google.config']['cliend_id']);
            $client->setClientSecret($app['google.config']['client_secret']);
            $client->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);

            $client->setRedirectUri($app['url_generator']->generate('socialauth', array(
                'name' => 'google'
            ), UrlGeneratorInterface::ABSOLUTE_URL));
            
            return $client;
        });
        
        $app['google.login_url'] = $app->share(function() use ($app) {
            return $app['google']->createAuthUrl();
        });
        
        $app['google.user'] = $app->share(function() use ($app) {
            $client = $app['google'];
            $client->authenticate($app['request']->get('code'));
            
            $oauth = new \Google_Service_Oauth2($client);
            return $oauth->userinfo->get();
        });
    }

    public function boot(Application $app)
    {
    }
}