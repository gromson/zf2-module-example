<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Controller\RoomsController;

class RoomsControllerFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $sl = $serviceLocator->getServiceLocator();

        return new RoomsController(
            $sl->get( 'Institution\Service\RoomsService' ),
            $sl->get( 'translator' ),
            $sl->get( 'datatablepluginmanager' ),
            $sl->get( 'formpluginmanager' ),
            $sl->get( 'Identity' )
        );
    }

}
