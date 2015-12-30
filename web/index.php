<?php

use Symfony\Component\HttpFoundation\Request;
use Knp\Menu\Matcher\Voter\UriVoter;

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;
$app['knp_menu.template'] = 'menu.twig';

// Register Providers
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(__DIR__ . '/../views')
));
$app->register(new Knp\Menu\Integration\Silex\KnpMenuServiceProvider());
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));

// Create menu
$app['main_menu'] = function($app) {
    $menu = $app['knp_menu.factory']->createItem('root', array('childAttributes' => array('class', 'teste')));
    $menu->setChildrenAttribute('class', 'nav navbar-nav');

    $menu->addChild('Home', array('route' => 'homepage'));
    $menu->addChild('Contact', array('route' => 'contact'));

    return $menu;
};
$app['knp_menu.menus'] = array('main' => 'main_menu');

$app->before(function (Request $request) use ($app) {
	$app['knp_menu.matcher']->addVoter(new UriVoter($request->getRequestUri()));
});

// Homepage controller
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
})
->bind('homepage');

// Contact controller
$app->get('/contact', function () use ($app) {
    return $app['twig']->render('contact.twig');
})
->bind('contact');

$app->run();