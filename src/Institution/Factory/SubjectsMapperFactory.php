<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\SubjectsSqlMapper;

class SubjectsMapperFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new SubjectsSqlMapper( $serviceLocator->get( 'Application\Hydrator\ClassPrefixArraySerializable' ), $serviceLocator->get( 'DbAdapter' ) );
	}

}
