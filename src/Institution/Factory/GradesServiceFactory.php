<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\GradesService;

class GradesServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new GradesService( $serviceLocator->get( 'Identity' ), $serviceLocator->get( 'Institution\Mapper\GradesMapper' ), $serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' ) );
	}

}
