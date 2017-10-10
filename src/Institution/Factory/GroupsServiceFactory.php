<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\GroupsService;

class GroupsServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new GroupsService( $serviceLocator->get( 'Identity' ), $serviceLocator->get( 'Institution\Mapper\GroupsMapper' ), $serviceLocator->get( 'Institution\Service\GradesService' ), $serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' ) );
	}

}
