<?php

namespace QuoteDB\Provider;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Silex\Application;
use Silex\ServiceProviderInterface;

class DoctrineORMServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $app['orm.em'] = $this->createEntityManager($app);
        
        
    }

    public function boot(Application $app)
    {
    }

    /**
     * Get the Entity Manager. Use the Doctrine ORM for manage entities on database.
     * 
     * - Create database connection object with config file data
     * - Set entity namespace
     * 
     * @return \Doctrine\ORM\EntityManager The Entity Manager.
     */
    private function createEntityManager($app)
    {
        $dbalConfiguration = new Configuration();
        $databaseParameters = $app['config']['db'];
        
        $driveOptions = array();
        if (!empty($databaseParameters["driverOptions"]) && is_array($databaseParameters["driverOptions"])) {
        	foreach ($databaseParameters["driverOptions"] as $option) {
        		$driveOptions[$option["code"]] = $option["value"];
        	}
        }
        
        $databaseParameters["driverOptions"] = $driveOptions;
        
        $connection = DriverManager::getConnection($databaseParameters, $dbalConfiguration);
        
        $entityManagerConfig = Setup::createConfiguration(true);
        
        AnnotationRegistry::registerFile(__DIR__ . "/../../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        AnnotationRegistry::registerFile(__DIR__ . "/../Annotation/Driver/SymfonyValidator.php");
        AnnotationRegistry::registerFile(__DIR__ . "/../Annotation/Driver/SymfonyDoctrineBridgeValidator.php");
        $reader = new AnnotationReader();
        $driverImpl = new AnnotationDriver($reader, __DIR__ . '/../Entity');
        $entityManagerConfig->setMetadataDriverImpl($driverImpl);
        
        // Set an alias for the entities
        $entityManagerConfig->addEntityNamespace('QuoteDB', 'QuoteDB\\Entity');
                
        return EntityManager::create($connection, $entityManagerConfig);
    }
}