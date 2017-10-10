<?php

namespace Institution\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Validator\Callback;
use Institution\Service\GroupsServiceInterface;

class SubjectForm extends Form implements InputFilterProviderInterface
{

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
     * @var array
     */
    protected $areas;

    /**
     *
     * @var array
     */
    protected $schoolLevelOptions = [
        GroupsServiceInterface::ELEMENTARY_SCHOOL => 'Elementary School',
        GroupsServiceInterface::MIDDLE_SCHOOL => 'Middle School',
        GroupsServiceInterface::HIGH_SCHOOL => 'High School',
    ];

    /**
     *
     * @param AdapterInterface $dbAdapter
     * @param array            $accountId
     * @param array            $areas
     * @param string           $name
     * @param string           $save_button_text
     */
    public function __construct(
        AdapterInterface $dbAdapter,
        $accountId,
        array $areas,
        $name = 'subject-form',
        $save_button_text = 'Save'
    )
    {
        parent::__construct($name);

        $this->dbAdapter = $dbAdapter;
        $this->accountId = $accountId;
        $this->areas = $areas;

        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'id',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => [
                    'id' => 'subject-id',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'subject_areas_id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => [
                    'class' => 'form-control',
                    'id' => 'subject-areas-id',
                    'required' => 'required'
                ],
                'options' => [
                    'label' => 'Subject Area',
                    'value_options' => $this->areas
                ]
            ]
        );

        $this->add(
            [
                'name' => 'title',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => [
                    'class' => 'form-control',
                    'id' => 'subject-title',
                    'placeholder' => 'Title',
                    'required' => 'required',
                ],
                'options' => [
                    'label' => 'Title',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'title_short',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => [
                    'class' => 'form-control',
                    'id' => 'subject-title-short',
                    'placeholder' => 'Short Title',
                    'required' => false,
                    'maxlength' => 9
                ],
                'options' => [
                    'label' => 'Short Title',
                    'hint' => '9 symbols max. This title will be shown in places with limited space.'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'level',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => [
                    'class' => 'form-control',
                    'id' => 'subject-level',
                    'placeholder' => 'Difficulty Level',
                    'required' => false,
                ],
                'options' => [
                    'label' => 'Difficulty Level',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'school_level',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'attributes' => [
                    'value' => []
                ],
                'options' => [
                    'label' => 'Classes',
                    'value_options' => $this->schoolLevelOptions,
                ]
            ]
        );

        $this->add(
            [
                'type' => 'Zend\Form\Element\Collection',
                'name' => 'programs',
                'options' => [
                    'label' => 'Traning Programs',
                    'count' => 0,
                    'should_create_template' => true,
                    'allow_add' => true,
                    'allow_remove' => true,
                    'target_element' => new SubjectFormProgramsFieldset($this->dbAdapter),
                ],
            ]
        );

        $this->add(
            [
                'type' => 'Zend\Form\Element\Csrf',
                'name' => 'csrf'
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => 'Zend\Form\Element\Button',
                'attributes' => [
                    'id' => 'subject-submit-button',
                    'type' => 'submit'
                ],
                'options' => [
                    'label' => $save_button_text,
                ]
            ]
        );

        $this->setValidationGroup(
            [
                'csrf',
                'id',
                'subject_areas_id',
                'title',
                'title_short',
                'level',
                'school_level',
                'programs' => [
                    'id',
                    'title',
                    'duration',
                    'authors'
                ]
            ]
        );
    }

    public function populateValues($data, $onlyBase = false)
    {
        foreach ($data as $key => $value) {
            if ($key === 'school_level' && is_string($value)) {
                $data[$key] = explode(',', $value);
            }
        }

        parent::populateValues($data, $onlyBase);
    }

    public function getInputFilterSpecification()
    {
        return [
            'id' => [
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
            ],
            'subject_areas_id' => [
                'required' => true,
                'filter' => [
                    ['name' => 'ToInt']
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => array_keys($this->areas),
                            'messages' => [
                                'notInArray' => 'Undefined subject area has been given!'
                            ],
                        ],
                    ]
                ]
            ],
            'title' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'isTitleAvailable'],
                            'messages' => [
                                Callback::INVALID_VALUE => 'The subject with the given title is already exists!'
                            ],
                        ]
                    ]
                ]
            ],
            'title_short' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'isShortTitleAvailable'],
                            'messages' => [
                                Callback::INVALID_VALUE => 'The subject with the given short title is already exists!'
                            ],
                        ]
                    ]
                ]
            ],
            'level' => [
                'required' => false,
                'filters' => [
                    ['name' => 'Int'],
                ],
                'validators' => [
                    [
                        'name' => 'LessThan',
                        'options' => [
                            'max' => 13,
                            'inclusive' => true
                        ]
                    ]
                ]
            ],
            'school_level' => [
                'required' => false,
                //                'validators' => [
                //                    [
                //                        'name'    => 'InArray',
                //                        'options' => array(
                //                            'haystack' => array_keys( $this->schoolLevelOptions ),
                //                            'messages' => array(
                //                                'notInArray' => 'Undefined classes has been given!'
                //                            ),
                //                        ),
                //                    ]
                //                ]
            ],
            'csrf' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'Csrf',
                        'options' => [
                            'timeout' => 24 * 3600
                        ]
                    ]
                ]
            ]
        ];
    }

    public function isTitleAvailable($value, $data)
    {
        return $this->isNotDuplicated('title', $value, $data);
    }

    public function isShortTitleAvailable($value, $data)
    {
        return $this->isNotDuplicated('title_short', $value, $data);
    }

    protected function isNotDuplicated($field, $value, $data)
    {
        $sql = new Sql($this->dbAdapter, 'subjects');
        $select = $sql->select()->columns(['id']);
        $where = $select->where->equalTo('accounts_id', $this->accountId)->equalTo($field, $value)->equalTo(
            'deleted',
            0
        );

        if ($data['id']) {
            $where->notEqualTo('id', $data['id']);
        }

        $select->where($where);

        $result = $this->dbAdapter->query($sql->buildSqlString($select), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        if ($result->count() === 0) {
            return true;
        } else {
            return false;
        }
    }

}
