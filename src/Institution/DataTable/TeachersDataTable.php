<?php

namespace Institution\DataTable;

use DataTable\Data\Filter;
use DataTable\Data\Table;
use DataTable\Data\Column;
use DataTable\Data\ColumnButton;
use DataTable\Render\Filter\SelectField;
use ZfcRbac\Service\AuthorizationServiceInterface;
use Zend\I18n\Translator\TranslatorInterface;

class TeachersDataTable extends Table
{

    public function __construct(
        AuthorizationServiceInterface $authorizationService,
        TranslatorInterface $translator,
        $data = null
    )
    {
        if ($data) {
            $this->setData($data);
        }

        $filterRenderer = new \DataTable\Render\Filter();
        $filterRenderer->setColumns(3);

        $this->setFilter(
            new Filter(
                [
                    'fields' => [
                        [
                            'name' => 'fullname',
                            'title' => 'Full Name'
                        ],
                        [
                            'name' => 'subjects',
                            'title' => 'Subjects'
                        ],
                        [
                            'name' => 'email',
                            'title' => 'Email'
                        ],
                        [
                            'name' => 'phone',
                            'title' => 'Phone'
                        ],
                        [
                            'name' => 'phone',
                            'title' => 'Phone'
                        ],
                        [
                            'name' => 'vacancy',
                            'title' => 'Vacancy',
                            'options' => [
                                [
                                    'value' => 1,
                                    'label' => $translator->translate('Vacancy', 'teachers'),
                                ],
                                [
                                    'value' => 0,
                                    'label' => $translator->translate('Occupied', 'teachers'),
                                ]
                            ],
                            'renderer' => new SelectField()
                        ],
                        [
                            'name' => 'dismissed',
                            'title' => 'Dismissed',
                            'options' => [
                                [
                                    'value' => 1,
                                    'label' => $translator->translate('Dismissed', 'teachers')
                                ],
                                [
                                    'value' => 0,
                                    'label' => $translator->translate('Employed', 'teachers'),
                                    'default' => true
                                ]
                            ],
                            'renderer' => new SelectField()
                        ]
                    ],
                    'renderer' => $filterRenderer
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'fullname',
                    'title' => 'Full Name',
                    'value' => function ($object) use ($translator) {
                        $result = '';
                        if ($object->vacancy) {
                            $result .= $translator->translate('Vacancy', 'teachers') . ' ';
                        }
                        $result .= sprintf(
                            '%2$s %1$s %3$s',
                            $object->firstname,
                            $object->lastname,
                            $object->middlename
                        );

                        if ($object->dismissed) {
                            return '<span style="text-decoration: line-through">' . $result . '</span>';
                        } else {
                            return $result;
                        }

                    }
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'subjects',
                    'title' => 'Subjects',
                    'value' => function ($object) {
                        $subjects = (array) $object->subject;
                        $html = '';
                        $i = 0;
                        foreach ($subjects as $subject) {
                            $html .= ($i++ ? ', ' : '') . $subject->title;
                        }

                        if ($object->dismissed) {
                            return '<span style="text-decoration: line-through">' . $html . '</span>';
                        } else {
                            return $html;
                        }
                    },
                    'cellHtmlOptions' => ['style' => 'max-width:600px;']
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'email',
                    'title' => 'Email',
                    'value' => function ($object) {
                        if ($object->dismissed) {
                            return '<span style="text-decoration: line-through">' . $object->email . '</span>';
                        } else {
                            return $object->email;
                        }
                    }
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'phone',
                    'title' => 'Phone',
                    'cellHtmlOptions' => ['style' => 'white-space: nowrap;'],
                    'value' => function ($object) {
                        if ($object->dismissed) {
                            return '<span style="text-decoration: line-through">' . $object->phone . '</span>';
                        } else {
                            return $object->phone;
                        }
                    }
                ]
            )
        );

        $this->addColumn(
            new ColumnButton(
                [
                    'name' => 'actions',
                    'buttons' => [
                        [
                            'name' => 'edit',
                            'icon' => 'fa fa-pencil',
                            'tooltip' => 'Edit the teacher',
                            'class' => 'btn btn-primary btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'teacher-edit'
                            ],
                            'visible' => function ($object) use ($authorizationService) {
                                return $authorizationService->isGranted(
                                        'teachers.update',
                                        $object
                                    ) && !$object->vacancy && !$object->dismissed;
                            }
                        ],
                        [
                            'name' => 'fill_vacancy',
                            'icon' => 'fa fa-file-o',
                            'tooltip' => 'Fill the vacancy',
                            'class' => 'btn btn-primary btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'teacher-edit'
                            ],
                            'visible' => function ($object) use ($authorizationService) {
                                return $authorizationService->isGranted(
                                        'teachers.update',
                                        $object
                                    ) && $object->vacancy == true;
                            }
                        ],
                        [
                            'name' => 'dismiss',
                            'icon' => 'fa fa-user-times',
                            'tooltip' => 'Dismiss the teacher',
                            'class' => 'btn btn-warning btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'teacher-dismiss'
                            ],
                            'visible' => function ($object) use ($authorizationService) {
                                return $authorizationService->isGranted(
                                        'teachers.dismiss',
                                        $object
                                    ) && !$object->vacancy && !$object->dismissed;
                            }
                        ],
                        [
                            'name' => 'delete',
                            'icon' => 'fa fa-trash',
                            'tooltip' => 'Delete the teacher',
                            'class' => 'btn btn-danger btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'teacher-delete'
                            ],
                            'visible' => function ($object) use ($authorizationService) {
                                return $authorizationService->isGranted('teachers.delete', $object);
                            }
                        ]
                    ]
                ]
            )
        );

        $this->setShowColumnsFilter(true);
        $this->setRoute(['route' => 'teachers']);
        $this->setHtmlOptions(['id' => 'teachers-data-table']);
        $this->setWrapperHtmlOptions(['id' => 'teachers-data-table-wrapper']);
        $this->setJavaScriptHandler($this->getScriptHandler());
//		$this->setJavaScriptFile( '/js/modalWindow.js' );
    }

    protected function getScriptHandler()
    {
        ob_start();
        include __DIR__ . '/../../../data/js/teachers.datatable.js';
        $script = ob_get_contents();
        ob_end_clean();

        return $script;
    }

}
