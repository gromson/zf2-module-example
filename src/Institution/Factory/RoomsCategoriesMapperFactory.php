<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\RoomsCategoriesSqlMapper;

class RoomsCategoriesMapperFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new RoomsCategoriesSqlMapper( $serviceLocator->get( 'DbAdapter' ), null, null );
	}

}
