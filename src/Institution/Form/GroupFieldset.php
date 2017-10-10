<?php

namespace Institution\Form;

use Zend\Filter\ToNull;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class GroupFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct( 'group' );

        $this->add(
            [
                'name'       => 'id',
                'type'       => 'Zend\Form\Element\Hidden',
                'attributes' => [
                    'class' => 'group-id',
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'level_up',
                'type'       => 'Zend\Form\Element\Hidden',
                'attributes' => [
                    'id'       => uniqid( 'group-level-up' ),
                    'class'    => 'group-level-up',
                    'required' => false,
                ],
                'options'    => [
                    'label' => 'Yes'
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'final',
                'type'       => 'Zend\Form\Element\Hidden',
                'attributes' => [
                    'id'       => uniqid( 'group-final' ),
                    'class'    => 'group-final',
                    'required' => false,
                ],
                'options'    => [
                    'label' => "No, don't ask me again"
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'letter',
                'type'       => 'Zend\Form\Element\Text',
                'attributes' => [
                    'class'     => 'form-control group-letter',
                    'maxlength' => 1,
                    'required'  => true,
                ],
                'options'    => [
                    'label' => 'Letter',
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'title',
                'type'       => 'Zend\Form\Element\Text',
                'attributes' => [
                    'class'       => 'form-control group-title',
                    'placeholder' => 'Title',
                    'required'    => false,
                ],
                'options'    => [
                    'label' => 'Title',
                ]
            ]
        );

        $this->add(
            [
                'name' => 'activeGrade',
                'type' => 'Institution\Form\GradeFieldset'
            ]
        );

        $this->add(
            [
                'name'       => 'note',
                'type'       => 'Zend\Form\Element\Hidden',
                'attributes' => [
                    'required' => false,
                ],
                'options'    => [
                    'label' => 'Note',
                ]
            ]
        );

        $this->add(
            [
                'name'       => 'delete',
                'type'       => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'required' => false,
                    'class'    => 'group-delete'
                ],
                'options'    => [
                    'label' => ' ',
                    'color' => 'danger'
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
            'id'             => [
                'required' => false,
                'filters'  => [
                    [ 'name' => 'ToInt' ],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
            ],
            'level_up'       => [
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
            ],
            'letter'         => [
                'required' => false,
                'filters'  => [
                    [ 'name' => 'StripTags' ],
                    [ 'name' => 'StringTrim' ],
                ],
            ],
            'title'          => [
                'required' => false,
                'filters'  => [
                    [ 'name' => 'StripTags' ],
                    [ 'name' => 'StringTrim' ],
                ],
            ],
            'note'           => [
                'required' => false,
                'filters'  => [
                    [ 'name' => 'StripTags' ],
                    [ 'name' => 'StringTrim' ],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
            ],
            'delete'         => [
                'required' => false,
                'filters'  => [
                    [ 'name' => 'ToInt' ],
                ]
            ]
        ];
    }

}
