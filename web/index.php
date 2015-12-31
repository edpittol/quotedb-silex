<?php

use Knp\Menu\Matcher\Voter\UriVoter;
use QuoteDB\Entity\User;
use QuoteDB\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$filename = __DIR__ .preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
	return false;
}

require_once __DIR__ .'/../app/bootstrap.php';
    
// Before register providers configuration
$app['knp_menu.template'] = 'menu.twig';
$app['security.firewalls'] = array(
    'default' => array(
        'pattern' => '^/',
        'anonymous' => true,
        'form' => array(
            'login_path' => '/',
            'check_path' => '/login_check',
            'use_referer' => true
        ),
        'logout' => array(
            'logout_path' => '/logout',
            'invalidate_session' => false
        ),
        'users' => $app->share(function ($app) {
            return $app['orm.em']->getRepository('QuoteDB:User');
		}),
	),
);

// Register Providers
$app->register(new Silex\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => array(__DIR__ .'/../views')
));
$app->register(new Knp\Menu\Integration\Silex\KnpMenuServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'pt_BR',
    'translator.domains' => array(),
));

$app['security.authentication.logout_handler.default'] = $app->share(function ($app) {
    return new QuoteDB\Handler\LogoutSuccessHandler($app['security.http_utils'], $app['session']);
});

$app['security.authentication.success_handler.default'] = $app->share(function ($app) {
    return new QuoteDB\Handler\AuthenticationSuccessHandler($app['security.http_utils'], $app['session']);
});

// Twig extend
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
	// Add global variables
	$twig->addGlobal('last_username', $app['session']->get('_security.last_username'));

	return $twig;
}));

// Create menu
$app['main_menu'] = function ($app) {
	$menu = $app['knp_menu.factory']->createItem('root', array('childAttributes' => array('class', 'teste')));
	$menu->setChildrenAttribute('class', 'nav navbar-nav');

	$menu->addChild('Home', array('route'    => 'homepage'));
	$menu->addChild('Contact', array('route' => 'contact'));

	return $menu;
};
$app['knp_menu.menus'] = array('main' => 'main_menu');

$app['security.role_hierarchy'] = array(
	'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['twig.form.templates'] = array('bootstrap_3_layout.html.twig');

// translate config files
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', __DIR__ . '/../locales/pt_BR.yml', 'pt_BR');

    return $translator;
}));

$app->before(function (Request $request) use ($app) {    
	$app['knp_menu.matcher']->addVoter(new UriVoter($request->getRequestUri()));

	$securityLastError = $app['security.last_error']($request);
	if (!empty($securityLastError)) {
		$app['session']->getFlashBag()->add('error', $securityLastError);
	}
});

// Homepage controller
$app->get('/', function () use ($app) {
	return $app['twig']->render('index.twig');
})
->bind('homepage');

// Contact controller
$app->get('/contact', function (Request $request) use ($app) {    
	return $app['twig']->render('contact.twig');
})
->bind('contact');

// register controller
$app->match('/register', function (Request $request) use ($app) {
    $user = new User();
    $form = $app['form.factory']->create(UserType::class, $user);
    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $password = $app['security.encoder_factory']->getEncoder($user)->encodePassword($user->getPlainPassword(), $user->getSalt());
        $user->setPassword($password);
    
        // persist user on database
        $em = $app['orm.em'];
        $em->persist($user);
        $em->flush();
        
        // login after registration
        $app['security.token_storage']->setToken(new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user, $user->getPassword(),
            'user_firewall',
            array('ROLE_USER')
            ));
        
        $app['session']->getFlashBag()->add('success', sprintf('Register successful.'));
    
        return $app->redirect('/');
    }
    
    return $app['twig']->render('register.twig', array(
        'form' => $form->createView()
    ));
})
->method('GET|POST')
->bind('register');

$app->run();