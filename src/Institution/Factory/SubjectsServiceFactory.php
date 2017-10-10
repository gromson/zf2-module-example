<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Service\SubjectsService;

class SubjectsServiceFactory implements FactoryInterface
{

	public function createService( ServiceLocatorInterface $serviceLocator )
	{
		return new SubjectsService( $serviceLocator->get( 'Identity' ), $serviceLocator->get( 'Institution\Mapper\SubjectsMapper' ), $serviceLocator->get( 'Institution\Mapper\SubjectsProgramsMapper' ), $serviceLocator->get( 'ZfcRbac\Service\AuthorizationService' ) );
	}

}
