<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Controller\TeachersController;

class TeachersControllerFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $sl = $serviceLocator->getServiceLocator();

        return new TeachersController(
            $sl->get( 'Institution\Service\TeachersService' ),
            $sl->get( 'Institution\Service\TeachersDismissService' ),
            $sl->get( 'Institution\Service\SubjectsService' ),
            $sl->get( 'datatablepluginmanager' ),
            $sl->get( 'formpluginmanager' ),
            $sl->get( 'Identity' )
        );
    }

}
