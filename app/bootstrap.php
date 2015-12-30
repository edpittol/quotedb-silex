<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

// Register shared providers
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/config.yml'));
$app->register(new QuoteDB\Provider\DoctrineORMServiceProvider());