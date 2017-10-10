<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\GradesSqlMapper;

class GradesMapperFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        return new GradesSqlMapper( $serviceLocator->get( 'DbAdapter' ) );
    }

}
