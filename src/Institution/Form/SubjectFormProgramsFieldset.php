<?php

namespace Institution\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Validator\Callback;

class SubjectFormProgramsFieldset extends Fieldset implements InputFilterProviderInterface
{

    /**
     *
     * @var AdapterInterface 
     */
    protected $dbAdapter;

    public function __construct( AdapterInterface $dbAdapter )
    {
        parent::__construct( 'programs' );

        $this->dbAdapter = $dbAdapter;

        $this->add( array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'subject-program-id',
            )
        ) );

        $this->add( array(
            'name' => 'subjects_id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'class' => 'subject-program-subjects-id',
            )
        ) );

        $this->add( array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control subject-program-title',
                'placeholder' => 'Title',
                'required' => 'required',
            ),
        ) );

        $this->add( array(
            'name' => 'duration',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control subject-program-duration',
                'placeholder' => 'Duration',
                'required' => false,
            ),
        ) );

        $this->add( array(
            'name' => 'authors',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control subject-program-authors',
                'placeholder' => 'Authors',
                'required' => false,
            ),
        ) );
    }

    /**
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'id' => array(
                'required' => true,
                'filters' => [
                    [ 'name' => 'ToInt' ],
                ],
            ),
            'title' => [
                'required' => true,
                'filters' => array(
                    array( 'name' => 'StripTags' ),
                    array( 'name' => 'StringTrim' ),
                ),
                'validators' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'isTitleAvailable' ],
//                            'callbackOptions' => ['subject_id' => $this->subjectId ],
                            'messages' => array(
                                Callback::INVALID_VALUE => 'The traning program with the given title is already exists!'
                            ),
                        ]
                    ]
                ]
            ],
            'duration' => [
                'required' => false,
                'filters' => [
                    [ 'name' => 'ToInt' ],
                ],
            ],
            'authors' => [
                'required' => false,
                'filters' => array(
                    array( 'name' => 'StripTags' ),
                    array( 'name' => 'StringTrim' ),
                ),
            ],
        ];
    }

    public function isTitleAvailable( $value, $data )
    {
        $sql = new Sql( $this->dbAdapter, 'subjects_programs' );
        $select = $sql->select()->columns( ['id' ] );
        $where = $select->where->equalTo( 'subjects_id', $data['subjects_id'] )->equalTo( 'title', $value )->equalTo( 'deleted', 0 );

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

}
