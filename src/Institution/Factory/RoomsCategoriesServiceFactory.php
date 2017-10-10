<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\RoomsCategoriesService;

class RoomsCategoriesServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new RoomsCategoriesService( $serviceLocator->get( 'Identity' ), $serviceLocator->get( 'Institution\Mapper\RoomsCategoriesMapper' ) );
	}

}
