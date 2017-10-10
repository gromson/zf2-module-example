<?php

namespace Institution\Form;

use Zend\Filter\ToNull;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\InArray;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Validator\Callback;

class RoomForm extends Form implements InputFilterProviderInterface
{

    /**
     *
     * @var array
     */
    protected $categories;

    /**
     *
     * @var array
     */
    protected $subjects;

    /**
     *
     * @var AdapterInterface 
     */
    protected $dbAdapter;

    /**
     *
     * @var int
     */
    protected $accountId;

    /**
     *
     * @param string $name
     * @param string $save_button_text
     */
    public function __construct( AdapterInterface $dbAdapter, $accountId, array $categories = [ ], array $subjects = [ ], $name = 'room-form', $save_button_text = 'Save' )
    {
        parent::__construct( $name );

        $this->dbAdapter = $dbAdapter;
        $this->categories = $categories;
        $this->subjects = $subjects;
        $this->accountId = $accountId;

        $this->setAttribute( 'method', 'post' );

        $this->add( array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'id' => 'room-id',
            ),
        ) );

        $this->add( array(
            'name' => 'number',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'room-number',
                'placeholder' => 'Number',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Number',
            ),
        ) );

        $this->add( array(
            'name' => 'capacity',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'room-capacity',
                'placeholder' => 'Capacity',
            ),
            'options' => array(
                'label' => 'Capacity',
            ),
        ) );

        $this->add( array(
            'name' => 'comment',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'room-comment',
                'placeholder' => 'Comment',
            ),
            'options' => array(
                'label' => 'Comment',
            ),
        ) );

        $this->add( array(
            'name' => 'rooms_categories_id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'rooms-categories-id',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Category',
                'value_options' => $this->categories
            ),
        ) );

        $this->add( array(
            'name' => 'subjects_id',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'rooms-subjects-id',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Subjects',
                'value_options' => $this->subjects
            ),
        ) );

        $this->add( array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'id' => 'room-submit-button',
                'type' => 'submit'
            ),
            'options' => [
                'label' => $save_button_text,
            ]
        ) );

        $this->setValidationGroup( [
            'id',
            'number',
            'capacity',
            'comment',
            'rooms_categories_id',
            'subjects_id'
        ] );
    }

    public function getInputFilterSpecification()
    {
        return array(
            'id' => array(
                'required' => true,
                'filters' => array(
                    array( 'name' => 'ToInt' ),
                ),
            ),
            'number' => array(
                'required' => true,
                'filters' => array(
                    array( 'name' => 'StripTags' ),
                    array( 'name' => 'StringTrim' ),
                ),
                'validators' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'isRoomNumberAvailable' ],
                            'messages' => array(
                                Callback::INVALID_VALUE => 'The room with given number is already exists!'
                            ),
                        ]
                    ]
                ]
            ),
            'capacity' => array(
                'required' => false,
                'filters' => [
                    ['name' => 'ToInt' ],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
                'validators' => array(
                    array( 'name' => 'IsInt' ),
                ),
            ),
            'comment' => [
                'required' => false,
                'filters' => [
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
            'rooms_categories_id' => [
                'required' => true,
                'filters' => array(
                    array( 'name' => 'ToInt' ),
                ),
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => array_keys( $this->categories ),
                            'messages' => array(
                                'notInArray' => 'Undefined category has been given!'
                            ),
                        ),
                    ),
                ),
            ],
            'subjects_id' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'Explode',
                        'options' => [
                            'validator' => new InArray( [
                                'haystack' => $this->getSubjectsValidationArray(), //array_keys( $this->subjects ),
                                'messages' => [
                                    'notInArray' => 'Undefined subject has been given!'
                                ] ]
                            )
                        ],
                    ],
                ],
            ]
        );
    }

    public function isRoomNumberAvailable( $value, $data )
    {
        $sql = new Sql( $this->dbAdapter, 'rooms' );
        $select = $sql->select()->columns( ['id' ] );
        $where = $select->where->equalTo( 'accounts_id', $this->accountId )->equalTo( 'number', $value )->equalTo( 'deleted', 0 );

        if ( $data['id'] ) {
            $where->notEqualTo( 'id', $data['id'] );
        }

        $select->where( $where );

        $result = $this->dbAdapter->query( $sql->buildSqlString( $select ), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE );

        if ( $result->count() === 0 ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getSubjectsValidationArray()
    {
        return array_map(function($s){
            return $s['value'];
        }, $this->subjects);
    }

}
