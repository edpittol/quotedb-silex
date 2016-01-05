<?php

/**
 * Load Symfony Doctrine Validator constraints classes.
 */
use Symfony\Component\Finder\Finder;

$constraintsDir = __DIR__ . '/../../../../vendor/symfony/doctrine-bridge/Validator/Constraints';

$finder = new Finder();
$finder->files()->in($constraintsDir);

foreach ($finder as $file) {
    require_once $file->getRealpath();
}