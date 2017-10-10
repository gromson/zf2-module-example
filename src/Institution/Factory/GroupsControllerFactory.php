<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Controller\GroupsController;

class GroupsControllerFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $sl = $serviceLocator->getServiceLocator();

        return new GroupsController(
            $sl->get( 'Institution\Service\GroupsService' ),
            $sl->get( 'formpluginmanager' ),
            $sl->get( 'Identity' )
        );
    }

}
