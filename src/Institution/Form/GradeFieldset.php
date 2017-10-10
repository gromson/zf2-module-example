<?php

namespace Institution\Form;

use Zend\Filter\ToNull;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class GradeFieldset extends Fieldset implements InputFilterProviderInterface
{

	public function __construct()
	{
		parent::__construct( 'activeGrade' );

//        $this->setHydrator( new ArraySerializable() )
//            ->setObject( new Grade() );

		$this->add(
			[
				'name'       => 'isLastYear',
				'type'       => 'Zend\Form\Element\Hidden',
				'attributes' => [
					'class'    => 'group-is-last-year',
					'required' => false,
				],
			]
		);

		$this->add(
			[
				'name'       => 'level',
				'type'       => 'Zend\Form\Element\Hidden',
				'attributes' => [
					'class'    => 'group-level',
					'required' => true,
				],
			]
		);

		$this->add(
			[
				'name'       => 'begin_year',
				'type'       => 'Zend\Form\Element\Hidden',
				'attributes' => [
					'class'    => 'group-year-begin',
					'required' => false,
				],
			]
		);

		$this->add(
			[
				'name'       => 'students_count',
				'type'       => 'Zend\Form\Element\Text',
				'attributes' => [
					'class'    => 'form-control group-students-count',
					'required' => false,
				],
				'options'    => [
					'label' => 'Number Of Students',
				]
			]
		);

		$this->add(
			[
				'name'       => 'male_count',
				'type'       => 'Zend\Form\Element\Text',
				'attributes' => [
					'class'    => 'form-control group-male-count',
					'required' => false,
				],
				'options'    => [
					'label' => 'Number Of Male',
				]
			]
		);

		$this->add(
			[
				'name'       => 'female_count',
				'type'       => 'Zend\Form\Element\Text',
				'attributes' => [
					'class'    => 'form-control group-female-count',
					'required' => false,
				],
				'options'    => [
					'label' => 'Number Of Female',
				]
			]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getInputFilterSpecification()
	{
		return [
			'isLastYear'     => [
				'required' => false,
				'filters'  => [
					[ 'name' => 'ToInt' ],
				]
			],
			'level'          => [
				'required' => true,
				'filters'  => [
					[ 'name' => 'ToInt' ],
				]
			],
			'begin_year'     => [
				'required' => true,
				'filters'  => [
					[ 'name' => 'ToInt' ],
				],
			],
			'students_count' => [
				'required' => false,
				'filters'  => [
					[
						'name'    => 'ToNull',
						'options' => [
							'type' => ToNull::TYPE_ALL ^ ToNull::TYPE_INTEGER // xor
						]
					],
					[ 'name' => 'ToInt' ],
				]
			],
			'male_count'     => [
				'required' => false,
				'filters'  => [
					[
						'name'    => 'ToNull',
						'options' => [
							'type' => ToNull::TYPE_ALL ^ ToNull::TYPE_INTEGER // xor
						]
					],
					[ 'name' => 'ToInt' ],
				]
			],
			'female_count'   => [
				'required' => false,
				'filters'  => [
					[
						'name'    => 'ToNull',
						'options' => [
							'type' => ToNull::TYPE_ALL ^ ToNull::TYPE_INTEGER // xor
						]
					],
					[ 'name' => 'ToInt' ],
				]
			],
		];
	}

}
