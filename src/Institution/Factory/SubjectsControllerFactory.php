<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Controller\SubjectsController;

class SubjectsControllerFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $sl = $serviceLocator->getServiceLocator();

        return new SubjectsController(
            $sl->get( 'Institution\Service\SubjectsService' ),
            $sl->get( 'datatablepluginmanager' ),
            $sl->get( 'formpluginmanager' ),
            $sl->get( 'Identity' )
        );
    }

}
