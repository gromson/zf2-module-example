<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Mapper\GroupsSqlMapper;

class GroupsMapperFactory implements FactoryInterface
{

    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        return new GroupsSqlMapper( $serviceLocator->get( 'DbAdapter' ), $serviceLocator->get( 'Institution\Model\Group' ) );
    }

}
