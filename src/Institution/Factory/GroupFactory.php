<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Model\Group;

class GroupFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $identity = $serviceLocator->get( 'Identity' );
        $settingsService = $serviceLocator->get( 'Settings\Service\SettingsService' );

        $settings = $settingsService->get( $identity->getAccountId() );
        
        return new Group( $settings->academic_year_begin, $settings->academic_year_end );
    }

}
