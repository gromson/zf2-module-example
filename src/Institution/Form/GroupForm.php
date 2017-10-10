<?php

namespace Institution\Form;

use Zend\Filter\ToNull;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Institution\Service\GroupsServiceInterface;
use Institution\Model\Group;

class GroupForm extends Form implements InputFilterProviderInterface
{
	/**
	 *
	 * @var Group
	 */
	protected $group;
	
	/**
	 *
	 * @param string $name
	 * @param string $save_button_text
	 */
	public function __construct( Group $group, $name = 'group-form', $save_button_text = 'Save' )
	{
		parent::__construct( $name );

		$this->group = $group;

		$this->setAttribute( 'method', 'post' )
			->setAttribute( 'class', 'form-horizontal' );

		$this->add( array(
			'name' => 'accounts_id',
			'type' => 'Zend\Form\Element\Hidden',
			'attributes' => array(
				'class' => 'group-accounts-id',
			)
		) );

		$this->add( [
			'name' => 'year',
			'type' => 'Zend\Form\Element\Hidden',
			'attributes' => [
				'id' => 'group-academic-year',
				'value' => ( int ) date( 'Y' )
			]
		] );

		$this->get( 'year' )->setOptions( ['value_options' => $this->getYearsArray() ] );

		$this->add( array(
			'type' => 'Zend\Form\Element\Collection',
			'name' => 'groups',
			'options' => array(
				'label' => 'Group',
				'count' => 0,
				'should_create_template' => true,
				'allow_add' => true,
				'allow_remove' => true,
				'target_element' => new GroupFieldset(),
			),
		) );

		$this->add( array(
			'type' => 'Zend\Form\Element\Csrf',
			'name' => 'csrf'
		) );

		$this->add( array(
			'name' => 'submit',
			'type' => 'Zend\Form\Element\Button',
			'attributes' => array(
				'id' => 'group-submit-button',
				'type' => 'submit',
				'class' => 'btn btn-primary'
			),
			'options' => [
				'label' => $save_button_text,
			]
		) );

		$this->setValidationGroup( [
//			'csrf',
			'accounts_id',
			'year',
			'groups' => [
				'id',
				'level_up',
				'final',
				'letter',
				'title',
				'note',
				'delete',
				'activeGrade' => [
					'isLastYear',
					'level',
					'begin_year',
					'students_count',
					'male_count',
					'female_count',
				]
			]
		] );
	}

	public function getInputFilterSpecification()
	{
		return [
			'accounts_id' => [
				'required' => false,
				'filters' => [
					['name' => 'ToInt' ],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
				]
			],
			'year' => [
				'required' => true,
				'filters' => [
					['name' => 'ToInt' ]
				],
				'validators' => [
					['name' => 'IsInt' ]
				]
			],
			'csrf' => [
				'required' => false,
				'validators' => [
					[ 'name' => 'Csrf' ]
				]
			]
		];
	}

	public function getYearsArray()
	{
		$array = array_combine(
			range( $this->get( 'year' )->getValue() - 5, ( int ) date( 'Y' ) + 1 ), range( $this->get( 'year' )->getValue() - 5, ( int ) date( 'Y' ) + 1 )
		);

		return array_map( function($value) {
			return $value . '/' . ($value + 1);
		}, $array );
	}

	public function saveGroups( GroupsServiceInterface $service, int $accountsId )
	{
		foreach ( $this->getData()[ 'groups' ] as $data ) {
			$group = clone $this->group;
			$group->exchangeArray( $data );
            $group->accounts_id = $accountsId;
			$group->setIsNewRecord( !$group->isPkSet() );
			$service->save($group);
		}
	}

}
