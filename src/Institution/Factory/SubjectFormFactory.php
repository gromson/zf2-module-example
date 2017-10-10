<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Form\SubjectForm;

class SubjectFormFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     * @var array
     */
    protected $options = [
        'form_name' => 'subject-form',
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
        $sl = $serviceLocator->getServiceLocator();
        $dbAdapter = $sl->get( 'DbAdapter' );
        $identity = $sl->get( 'Identity' );
        
        $subjectsService = $sl->get('Institution\Service\SubjectsService');
        $areas = $subjectsService->fetchAreasForDropDown();

        return new SubjectForm( $dbAdapter, $identity->getAccountId(), $areas, $this->options['form_name'], $this->options['save_button_text'] );
    }

}
