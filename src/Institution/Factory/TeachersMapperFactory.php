<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\TeachersSqlMapper;

class TeachersMapperFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new TeachersSqlMapper( $serviceLocator->get( 'DbAdapter' ), null, null, $serviceLocator->get( 'Application\Hydrator\ClassPrefixArraySerializable' ) );
	}

}
