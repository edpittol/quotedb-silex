<?php

use Knp\Menu\Matcher\Voter\UriVoter;
use QuoteDB\Entity\Author;
use QuoteDB\Entity\Quote;
use QuoteDB\Entity\User;
use QuoteDB\Form\UserType;
use QuoteDB\Form\QuoteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use QuoteDB\Form\ContactType;
use QuoteDB\Entity\Contact;

$filename = __DIR__ .preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// active debug if the access is local
if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
    $app['debug'] = true;
}

// Register Providers
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));
$app->register(new QuoteDB\Provider\DoctrineORMServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
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
    )
));
$app->register(new QuoteDB\Provider\SecurityServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(__DIR__ .'/../views')
));
$app->register(new Knp\Menu\Integration\Silex\KnpMenuServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'pt_BR',
    'translator.domains' => array(),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new QuoteDB\Provider\FacebookServiceProvider());
$app->register(new QuoteDB\Provider\GoogleServiceProvider());
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $app['config']['log']['logfile'],
    'monolog.level' => $app['config']['log']['level'],
    'monolog.name' => 'quotedb',
));
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
    'swiftmailer.options' => $app['config']['smtp'],
));

$app['security.authentication.logout_handler.default'] = $app->share(function ($app) {
    return new QuoteDB\Handler\LogoutSuccessHandler($app['security.http_utils'], $app);
});

$app['security.authentication.success_handler.default'] = $app->share(function ($app) {
    return new QuoteDB\Handler\AuthenticationSuccessHandler($app['security.http_utils'], $app);
});

// Twig extend
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    // Add global variables
    $twig->addGlobal('last_username', $app['session']->get('_security.last_username'));
    
    // Filter functions
    $twig->addFilter(new Twig_SimpleFilter('count', function ($array) {
        return count($array);
    }));

    return $twig;
}));

// Create menu
$app['main_menu'] = function ($app) {
    $menu = $app['knp_menu.factory']->createItem('root', array('childAttributes' => array('class', 'teste')));
    $menu->setChildrenAttribute('class', 'nav navbar-nav');

    $menu->addChild($app['translator']->trans('Home'), array('route'    => 'homepage'));
    $menu->addChild($app['translator']->trans('Contact'), array('route' => 'contact'));

    return $menu;
};
$app['knp_menu.menus'] = array('main' => 'main_menu');

$app['security.role_hierarchy'] = array(
    'ROLE_ADMIN' => array('ROLE_USER'),
);

$app['twig.form.templates'] = array('bootstrap_3_layout.html.twig');

// translate config files
$app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', __DIR__ . '/../locales/pt_BR.yml', 'pt_BR');
    $translator->addResource('yaml', __DIR__ . '/../locales/pt_BR.yml', 'pt_BR', 'validators');

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
    $quote = new Quote();
    $form = $app['form.factory']->create(QuoteType::class, $quote, array(
        'action' => $app['url_generator']->generate('quote')
    ));
    
    $quotes = $app['orm.em']->getRepository('QuoteDB:Quote')->homepageQuotes();
    
    return $app['twig']->render('index.twig', array(
        'form' => $form->createView(),
        'quotes' => $quotes
    ));
})
->bind('homepage');

// Contact controller
$app->match('/contact', function (Request $request) use ($app) {
    $form = $app['form.factory']->create(ContactType::class, new Contact());
    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $contact = $form->getData();
        
        $subject = $app['translator']->trans('New Quote DB contact message');
        $body = sprintf(
            "Name: %s\nE-mail: %s\nMessage:\n%s",
            $contact->getName(),
            $contact->getEmail(),
            $contact->getMessage()
        );
        
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array('smtp@aztecweb.net'))
            ->setReplyTo(array($contact->getEmail()))
            ->setTo(array('aztec@aztecweb.net'))
            ->setBody($body);
        
        if ($app['mailer']->send($message)) {
            $app['session']->getFlashBag()->add('success', $app['translator']->trans('Message send successfully'));
            $app['monolog']->addInfo('Contact sent.');
            $form = $app['form.factory']->create(ContactType::class);
        } else {
            $app['session']->getFlashBag()->add('error', $app['translator']->trans('Error to send message'));
            $app['monolog']->addCritical('Error to send contact message.');
        }
        
        $app->redirect('contact');
    } else {
        foreach ($form->getErrors() as $error) {
            $app['session']->getFlashBag()->add('error', $error->getMessage());
        }
    }
    
    return $app['twig']->render('contact.twig', array(
        'form' => $form->createView()
    ));
})
->method('GET|POST')
->bind('contact');

// register controller
$app->match('/register', function (Request $request) use ($app) {
    $user = new User();
    $form = $app['form.factory']->create(UserType::class, $user);
    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $password = $app['security.encoder_factory']
            ->getEncoder($user)
            ->encodePassword($user->getPlainPassword(), $user->getSalt())
        ;
        $user->setPassword($password);
    
        // persist user on database
        $em = $app['orm.em'];
        $em->persist($user);
        $em->flush();
        
        $app['session']->getFlashBag()->add('success', $app['translator']->trans('Register successful.'));
        $app['monolog']->addInfo(
            sprintf("User '%s' registered with ID %d and form provider.", $user->getEmail(), $user->getId())
        );
        $app['user'] = $user;
        $app['security.token_storage']->setToken($app['security.set_token']);
    
        return $app->redirect('/');
    }
    
    return $app['twig']->render('register.twig', array(
        'form' => $form->createView()
    ));
})
->method('GET|POST')
->bind('register');

// OAuth2 authentication, allowed Facebook and Google
$app->get('/socialauth/{name}', function (Request $request, $name) use ($app) {
    try {
        $oauthUser = $app[$name . '.user'];

        $em = $app['orm.em'];
        try {
            $user = $em->getRepository('QuoteDB:User')->loadUserByUsername($oauthUser->getEmail());
        } catch (UsernameNotFoundException $e) {
            $user = new User();
            $user->setName($oauthUser->getName());
            $user->setEmail($oauthUser->getEmail());
            $em->persist($user);
            $em->flush();
        
            $app['session']->getFlashBag()->add('success', $app['translator']->trans('Register successful.'));
            $app['monolog']->addInfo(
                sprintf("User '%s' registered with ID %d and %s provider.", $user->getEmail(), $user->getId(), $name)
            );
        }
        
        $app['user'] = $user;
        $app['security.token_storage']->setToken($app['security.set_token']);
        
    } catch (InvalidArgumentException $e) {
        $app['session']->getFlashBag()->add('error', sprintf('Invalid social auth method.'));
    }
    
    return $app->redirect('/');
})
->bind('socialauth');

// authors json
$app->get('/authors', function (Request $request) use ($app) {
    $query = $request->query->get('query');
    $authors = $app['orm.em']->getRepository('QuoteDB:Author')->autocompleteQuery($query);
    
    return $app->json($authors, 201);
})
->bind('authors');

// insert quote controller
$app->post('/quote', function (Request $request) use ($app) {
    $quote = new Quote();
    $form = $app['form.factory']->create(QuoteType::class, $quote);
    
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $em = $app['orm.em'];
        
        // persist user on database
        $quote->setInsertedBy($app['user']);
        
        // find if author already exist
        $authorName = $quote->getAuthor();
        $author = $em->getRepository('QuoteDB:Author')->findBy(array(
            'name' => $authorName
        ));
        $author = $author[0];
        
        // if not exist, create
        if (empty($author)) {
            $author = new Author();
            $author->setName($authorName);
        }
        
        $quote->setAuthor($author);

        $em->persist($quote);
        $em->flush();
        
        $app['session']->getFlashBag()->add(
            'success',
            $app['translator']->trans('Quote registered with success. Wait for approve.')
        );
        $app['monolog']->addInfo(sprintf("Quote '%d' registered.", $quote->getId()));
    } else {
        $app['session']->getFlashBag()->add('error', $app['translator']->trans('Error on register quote. Try again.'));
        $app['monolog']->addWarning('Error on register quote.');
    }

    return $app->redirect('/');
})
->bind('quote');

$app->run();