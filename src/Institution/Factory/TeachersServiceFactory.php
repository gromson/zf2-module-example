<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\TeachersService;

class TeachersServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new TeachersService(
			$serviceLocator->get( 'Identity' ),
			$serviceLocator->get( 'Institution\Mapper\TeachersMapper' ),
			$serviceLocator->get( 'Users\Service\UsersService' ),
			$serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' )
		);
	}

}
