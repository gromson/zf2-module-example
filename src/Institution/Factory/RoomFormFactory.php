<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Form\RoomForm;

class RoomFormFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     * @var array
     */
    protected $options = [
        'form_name' => 'room-form',
        'save_button_text' => 'Save'
    ];

    /**
     * {@inheritDoc}
     */
    public function setCreationOptions( array $options )
    {
        $this->options = array_merge( $this->options, $options );
    }

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return UserForm
     */
    public function createService( ServiceLocatorInterface $serviceLocator )
    {
        $dbAdapter = $serviceLocator->getServiceLocator()->get( 'DbAdapter' );
        $categoriesService = $serviceLocator->getServiceLocator()->get( 'Institution\Service\RoomsCategoriesService' );
        $subjectsService = $serviceLocator->getServiceLocator()->get( 'Institution\Service\SubjectsService' );
        $categories = $categoriesService->fetchForDropDown();
        $subjects = $subjectsService->fetchForDropDown();
        $identity = $serviceLocator->getServiceLocator()->get( 'Identity' );
        
        return new RoomForm( $dbAdapter, $identity->getAccountId(), $categories, $subjects, $this->options['form_name'], $this->options['save_button_text'] );
    }

}
