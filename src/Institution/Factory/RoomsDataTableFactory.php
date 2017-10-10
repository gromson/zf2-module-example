<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\DataTable\RoomsDataTable;

class RoomsDataTableFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     * @var array
     */
    protected $options = [
        'data' => null,
        'url' => null
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
     * @return RoomsDataTable
     */
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $authorizationService = $serviceLocator->getServiceLocator()->get( 'ZfcRbac\Service\AuthorizationService' );
        $roomsCategoriesService = $serviceLocator->getServiceLocator()
            ->get( 'Institution\Service\RoomsCategoriesService' );
        $categories = $roomsCategoriesService->fetchForDropDown();

        return new RoomsDataTable( $categories, $authorizationService, $this->options['url'], $this->options['data'] );
    }

}
