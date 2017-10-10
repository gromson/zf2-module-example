<?php

namespace Institution\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Institution\Form\TeacherForm;

class TeacherFormFactory implements FactoryInterface, MutableCreationOptionsInterface
{

	/**
	 * @var array
	 */
	protected $options = [
		'form_name' => 'teacher-form',
		'save_button_text' => 'Save',
        'acceptable_user_id' => null
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
		$identity = $serviceLocator->getServiceLocator()->get( 'Identity' );
		$translator = $serviceLocator->getServiceLocator()->get( 'translator' );

		$subjectsService = $serviceLocator->getServiceLocator()->get( 'Institution\Service\SubjectsService' );
		$usersService = $serviceLocator->getServiceLocator()->get( 'Users\Service\UsersService' );
		$rolesService = $serviceLocator->getServiceLocator()->get( 'Users\Service\UsersRolesService' );

		$subjects = $subjectsService->fetchForDropDown();
		$users = $usersService->fetchForDropDown( true, $translator->translate( '- Create New -', 'teachers' ), '%1$s %2$s %3$s - %4$s', ['lastname', 'firstname', 'middlename', 'email' ], true, $this->options[ 'acceptable_user_id' ] );
		$roles = $rolesService->fetchForDropDown( $identity->getAccountId() );

		return new TeacherForm( $subjects, $users, $roles, $this->options[ 'form_name' ], $this->options[ 'save_button_text' ] );
	}

}
