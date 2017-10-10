<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\DataTable\TeachersDataTable;

class TeachersDataTableFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     * @var array
     */
    protected $options = [
        'data' => null,
        'url'  => null
    ];

    /**
     * {@inheritDoc}
     */
    public function setCreationOptions( array $options )
    {
        $this->options = array_merge( $this->options, $options );
    }

    /**
     * {@inheritDoc}
     * @return UsersDataTable
     */
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $authorizationService = $serviceLocator->getServiceLocator()->get( 'ZfcRbac\Service\AuthorizationService' );
        $translator           = $serviceLocator->getServiceLocator()->get( 'translator' );

        return new TeachersDataTable( $authorizationService, $translator, /*$this->options['url'],*/ $this->options['data'] );
    }

}
