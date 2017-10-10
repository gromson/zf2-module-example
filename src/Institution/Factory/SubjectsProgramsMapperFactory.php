<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\SubjectsProgramsSqlMapper;

class SubjectsProgramsMapperFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new SubjectsProgramsSqlMapper( $serviceLocator->get( 'Application\Hydrator\ClassPrefixArraySerializable' ), $serviceLocator->get( 'DbAdapter' ) );
	}

}
