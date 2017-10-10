<?php

namespace Institution\Form;

use Institution\Service\GroupsServiceInterface;
use Zend\Filter\ToNull;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\InArray;
use Zend\Validator\Callback;

class TeacherForm extends Form implements InputFilterProviderInterface
{

    /**
     *
     * @var array
     */
    protected $subjects;

    /**
     *
     * @var array
     */
    protected $users;

    /**
     *
     * @var array
     */
    protected $roles;

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
     * @param array $subjects
     * @param array $users
     * @param array $roles
     * @param string $name
     * @param string $save_button_text
     */
    public function __construct(
        array $subjects = [],
        array $users = [],
        array $roles = [],
        $name = 'teacher-form',
        $save_button_text = 'Save'
    )
    {
        parent::__construct($name);

        $this->subjects = $subjects;
        $this->users = $users;
        $this->roles = $roles;

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'id' => 'teacher-id',
                ),
            )
        );

        $this->add(
            array(
                'name' => 'firstname',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-firstname',
                    'placeholder' => 'First Name',
                    'required' => 'required',
                ),
                'options' => array(
                    'label' => 'First Name',
                ),
            )
        );

        $this->add(
            array(
                'name' => 'lastname',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-lastname',
                    'placeholder' => 'Last Name',
                    'required' => 'required',
                ),
                'options' => array(
                    'label' => 'Last Name',
                ),
            )
        );

        $this->add(
            array(
                'name' => 'middlename',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-middlename',
                    'placeholder' => 'Middle Name',
                ),
                'options' => array(
                    'label' => 'Middle Name',
                ),
            )
        );

        $this->add(
            array(
                'name' => 'subjects_id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-subjects-id',
                    'multiple' => 'multiple',
                ),
                'options' => array(
                    'label' => 'Subjects',
                    'value_options' => $this->subjects
                ),
            )
        );

        $this->add(
            [
                'name' => 'has_access',
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => [
                    'id' => 'teacher-has-access',
                ],
                'options' => [
                    'label' => 'Provide Access',
                    'hint' => 'You can provide an access to the system for the teacher to let him view his schedule. To do this you can choose an existing user or fill the information to create a new one.'
                ]
            ]
        );

        $this->add(
            [
                'name' => 'users_id',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-users-id',
                ),
                'options' => array(
                    'label' => 'User',
                    'value_options' => $this->users
                ),
            ]
        );

        $this->add(
            array(
                'name' => 'users_roles_code',
                'type' => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'user-role-code',
                    //				'required' => 'required',
                ),
                'options' => array(
                    'label' => 'Role',
                    'value_options' => $this->roles
                ),
            )
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
            array(
                'name' => 'email',
                'type' => 'Zend\Form\Element\Email',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-email',
                    'placeholder' => 'Email Address',
                    //				'required' => 'required',
                ),
                'options' => array(
                    'label' => 'Email',
                ),
            )
        );

        $this->add(
            array(
                'name' => 'phone',
                'type' => 'Zend\Form\Element\Text',
                'attributes' => array(
                    'class' => 'form-control',
                    'id' => 'teacher-phone',
                    'placeholder' => 'Phone Number',
                    'required' => false,
                ),
                'options' => array(
                    'label' => 'Phone Number',
                ),
            )
        );

        $this->add(
            [
                'type' => 'Zend\Form\Element\Csrf',
                'name' => 'csrf'
            ]
        );

        $this->add(
            array(
                'name' => 'submit',
                'type' => 'Zend\Form\Element\Button',
                'attributes' => [
                    'id' => 'teacher-submit-button',
                    'type' => 'submit',
                ],
                'options' => [
                    'label' => $save_button_text,
                ]
            )
        );

        $this->setValidationGroup(
            [
                'csrf',
                'id',
                'firstname',
                'lastname',
                'middlename',
                'subjects_id',
                'school_level',
                'users_id',
                'has_access',
                'users_roles_code',
                'email',
                'phone'
            ]
        );
    }

    public function getInputFilterSpecification()
    {
        return array(
            'id' => array(
                'required' => false,
                'filters' => array(
                    ['name' => 'ToInt'],
                ),
            ),
            'firstname' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ],
            ],
            'lastname' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim']
                ],
            ],
            'middlename' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
            ],
            'subjects_id' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'Explode',
                        'options' => [
                            'validator' => new InArray(
                                [
                                    'haystack' => $this->getAvailableSubjectIds(),
                                    'messages' => [
                                        'notInArray' => 'Undefined subject has been given!'
                                    ]
                                ]
                            )
                        ],
                    ],
                ],
            ],
            'school_level' => [
                'required' => true,
//                'validators' => [
//                    [
//                        'name' => 'InArray',
//                        'options' => array(
//                            'haystack' => array_keys($this->schoolLevelOptions),
//                            'messages' => array(
//                                'notInArray' => 'Undefined class level has been given!'
//                            ),
//                        ),
//                    ]
//                ]
            ],
            'users_id' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
                'validators' => array(
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => array_keys($this->users),
                            'messages' => [
                                'notInArray' => 'Undefined user has been given!'
                            ],
                        ],
                    ],
                ),
            ],
            'users_roles_code' => [
                'filters' => array(
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ),
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => array_keys($this->roles),
                            'messages' => array(
                                'notInArray' => 'Undefined role was given!'
                            ),
                        ),
                    ),
                )
            ],
            'email' => [
                'required' => false,
                'filters' => array(
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ),
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'messages' => [
                                'emailAddressInvalidFormat' => 'Email address format is not invalid!',
                            ]
                        ]
                    ],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => [$this, 'isEmailOfMandatoryRequirements'],
                            'messages' => array(
                                Callback::INVALID_VALUE => 'The Email field is required!'
                            ),
                        ]
                    ]
                ]
            ],
            'phone' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    [
                        'name' => 'ToNull',
                        'options' => [
                            'type' => ToNull::TYPE_ALL
                        ]
                    ],
                ],
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

    public function isEmailOfMandatoryRequirements($value, $data)
    {
        if ((int) $data['has_access'] === 1 && (int) $data['users_id'] === 0) {
            if (empty($value)) {
                return false;
            }
        }

        return true;
    }

    public function isConfigurationFullView()
    {
        return (
            (int) $this->get('users_id')->getValue() === 0 &&
            (int) $this->get('id')->getValue() === 0 &&
            (int) $this->get('has_access')->getValue() === 1
        );
    }

    public function isConfigurationHideNewUserFields()
    {
        return (int) $this->get('users_id')->getValue() > 0;
    }

    protected function getAvailableSubjectIds()
    {
        return array_map(
            function ($item) {
                return $item['value'];
            },
            $this->subjects
        );
    }

}
