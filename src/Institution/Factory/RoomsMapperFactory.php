<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\RoomsSqlMapper;
use Institution\Model\Room;

class RoomsMapperFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new RoomsSqlMapper( $serviceLocator->get( 'DbAdapter' ), null, null, $serviceLocator->get( 'Application\Hydrator\ClassPrefixArraySerializable' ) );
	}

}
