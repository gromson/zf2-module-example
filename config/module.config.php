<?php

return [
    'router' => [
        'routes' => [
            'institution' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/institution[/[:action]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => '',
                        'controller' => 'Institution\Controller\Institution',
                        'action' => 'index',
                    ],
                ],
            ],
            'subjects' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/subjects[/[:action[/[:id]]]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => '',
                        'controller' => 'Institution\Controller\Subjects',
                        'action' => 'list',
                    ],
                ],
            ],
            'rooms' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/rooms[/[:action[/[:id]]]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => '',
                        'controller' => 'Institution\Controller\Rooms',
                        'action' => 'list',
                    ],
                ],
            ],
            'teachers' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/teachers[/[:action[/[:id]]]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => '',
                        'controller' => 'Institution\Controller\Teachers',
                        'action' => 'list',
                    ],
                ],
            ],
            'groups' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/groups[/[:form_category]]',
                    'constraints' => [
                        'form_category' => '(elementary|middle|high)',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => '',
                        'controller' => 'Institution\Controller\Groups',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'action' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route' => '/groups/:action[/]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => '',
                                'controller' => 'Institution\Controller\Groups',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'Institution\Controller\Institution' => 'Institution\Factory\InstitutionControllerFactory',
            'Institution\Controller\Subjects' => 'Institution\Factory\SubjectsControllerFactory',
            'Institution\Controller\Rooms' => 'Institution\Factory\RoomsControllerFactory',
            'Institution\Controller\Teachers' => 'Institution\Factory\TeachersControllerFactory',
            'Institution\Controller\Groups' => 'Institution\Factory\GroupsControllerFactory',
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Institution\Mapper\SubjectsMapper' => 'Institution\Factory\SubjectsMapperFactory',
            'Institution\Mapper\SubjectsProgramsMapper' => 'Institution\Factory\SubjectsProgramsMapperFactory',
            'Institution\Service\SubjectsService' => 'Institution\Factory\SubjectsServiceFactory',
            'Institution\Mapper\RoomsMapper' => 'Institution\Factory\RoomsMapperFactory',
            'Institution\Service\RoomsService' => 'Institution\Factory\RoomsServiceFactory',
            'Institution\Mapper\RoomsCategoriesMapper' => 'Institution\Factory\RoomsCategoriesMapperFactory',
            'Institution\Service\RoomsCategoriesService' => 'Institution\Factory\RoomsCategoriesServiceFactory',
            'Institution\Mapper\TeachersMapper' => 'Institution\Factory\TeachersMapperFactory',
            'Institution\Service\TeachersService' => 'Institution\Factory\TeachersServiceFactory',
            'Institution\Service\TeachersDismissService' => \Institution\Factory\TeachersDismissServiceFactory::class,
            'Institution\Mapper\GroupsMapper' => 'Institution\Factory\GroupsMapperFactory',
            'Institution\Service\GroupsService' => 'Institution\Factory\GroupsServiceFactory',
            'Institution\Model\Group' => 'Institution\Factory\GroupFactory',
			'Institution\Mapper\GradesMapper' => 'Institution\Factory\GradesMapperFactory',
            'Institution\Service\GradesService' => 'Institution\Factory\GradesServiceFactory',
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'subject/form' => __DIR__ . '/../view/institution/subjects/partial/form.phtml',
            'room/form' => __DIR__ . '/../view/institution/rooms/partial/form.phtml',
            'teacher/form' => __DIR__ . '/../view/institution/teachers/partial/form.phtml',
            'groups/table' => __DIR__ . '/../view/institution/groups/partial/table.phtml',
        ],
        'template_path_stack' => [
            'institution' => __DIR__ . '/../view',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'institution'
            ],
            [
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../language/subjects',
                'pattern' => '%s.php',
                'text_domain' => 'subjects'
            ],
            [
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../language/rooms',
                'pattern' => '%s.php',
                'text_domain' => 'rooms'
            ],
            [
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../language/teachers',
                'pattern' => '%s.php',
                'text_domain' => 'teachers'
            ],
            [
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../language/groups',
                'pattern' => '%s.php',
                'text_domain' => 'groups'
            ],
        ],
    ],
];
