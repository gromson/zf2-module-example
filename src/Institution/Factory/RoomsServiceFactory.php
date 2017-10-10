<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\RoomsService;

class RoomsServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new RoomsService(
			$serviceLocator->get( 'Identity' ),
			$serviceLocator->get( 'Institution\Mapper\RoomsMapper' ),
			$serviceLocator->get( 'Institution\Service\SubjectsService' ),
			$serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' )
		);
	}

}
