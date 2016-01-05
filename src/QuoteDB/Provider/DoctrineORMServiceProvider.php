<?php

namespace QuoteDB\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use QuoteDB\Database\EntityManagerFactory;

class DoctrineORMServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $app['orm.em'] = $app->share(function ($app) {
            return EntityManagerFactory::create($app['config']['db']);
        });
    }

    public function boot(Application $app)
    {
    }
}