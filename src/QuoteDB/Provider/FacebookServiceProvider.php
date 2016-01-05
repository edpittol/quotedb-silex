<?php

namespace QuoteDB\Provider;

use Facebook\Facebook;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FacebookServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $app['facebook'] = $app->share(function () use ($app) {
            return new Facebook(array(
                'app_id' => $app['facebook.config']['app_id'],
                'app_secret' => $app['facebook.config']['app_secret'],
                'default_graph_version' => 'v2.5'
            ));
        });
        
        $app['facebook.config'] = $app->share(function () use ($app) {
            return array(
                'app_id' => (string) $app['config']['social']['facebook']['app_id'],
                'app_secret' => (string) $app['config']['social']['facebook']['app_secret'],
            );
        });
        
        $app['facebook.login_url'] = $app->share(function () use ($app) {
            return $app['facebook']->getRedirectLoginHelper()->getLoginUrl(
                $app['url_generator']->generate('socialauth', array(
                    'name' => 'facebook'
                ), UrlGeneratorInterface::ABSOLUTE_URL),
                array('email')
            );
        });

        $app['facebook.access_token'] = $app->share(function () use ($app) {
            $accessToken = $app['facebook']->getRedirectLoginHelper()->getAccessToken();
            $oAuth2Client = $app['facebook']->getOAuth2Client();
            
            $tokenMetadata = $oAuth2Client->debugToken($accessToken);
            $tokenMetadata->validateAppId($app['facebook.config']['app_id']);
            $tokenMetadata->validateExpiration();
            
            if (!$accessToken->isLongLived()) {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            }
            
            return $accessToken;
        });

        $app['facebook.user'] = $app->share(function () use ($app) {
            $app['facebook']->setDefaultAccessToken($app['facebook.access_token']);
            $response = $app['facebook']->get('/me?fields=name,email');
            
            return $response->getGraphUser();
        });
    }

    public function boot(Application $app)
    {
    }
}