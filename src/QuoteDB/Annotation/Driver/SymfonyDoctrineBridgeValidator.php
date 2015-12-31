<?php

use Symfony\Component\Finder\Finder;

$constraintsDir = __DIR__ . '/../../../../vendor/symfony/validator/Constraints';

$finder = new Finder();
$finder->files()->in($constraintsDir);

foreach ($finder as $file) {
    require_once $file->getRealpath();
}