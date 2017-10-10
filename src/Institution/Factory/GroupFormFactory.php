<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Form\GroupForm;

class GroupFormFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     * @var array
     */
    protected $options = [
        'form_name' => 'group-form',
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
        $group = $serviceLocator->getServiceLocator()->get( 'Institution\Model\Group' );
        return new GroupForm( $group, $this->options['form_name'], $this->options['save_button_text'] );
    }

}
