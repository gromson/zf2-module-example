<?php

namespace Institution\DataTable;

use DataTable\Data\Table;
use DataTable\Data\Column;
use DataTable\Data\ColumnButton;
use Institution\DataTable\Render\RoomsColumnCategoriesRender;
use ZfcRbac\Service\AuthorizationServiceInterface;
use Zend\Mvc\Controller\Plugin\Url;

class RoomsDataTable extends Table
{

    public function __construct(
        array $categories,
        AuthorizationServiceInterface $authorizationService,
        Url $url,
        $data = null
    )
    {
        if ( $data ) {
            $this->setData( $data );
        }

        $this->addColumn(
            new Column(
                [
                    'name' => 'number',
                    'title' => 'Number',
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'rooms_categories_id',
                    'title' => 'Category',
                    'render' => new RoomsColumnCategoriesRender( $categories ),
                    'placeholder' => 'All',
                    'value' => function( $object ) {
                        return $object->roomCategory->title;
                    }
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'subjects',
                    'title' => 'Subjects',
                    'value' => function( $object ) {
                        $html = '';
                        $i = 0;
                        foreach ( $object->subjects as $subject ) {
                            $html .= ( $i++ ? ', ' : '' ) . $subject->title;
                        }

                        return $html;
                    }
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'capacity',
                    'title' => 'Capacity',
                ]
            )
        );

        $this->addColumn(
            new Column(
                [
                    'name' => 'comment',
                    'title' => 'Comment',
                    'cellHtmlOptions' => [
                        'style' => 'overflow: hidden;'
                    ]
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
                            'tooltip' => 'Edit the room',
                            'class' => 'btn btn-primary btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'room-edit'
                            ],
                            'visible' => function( $object ) use ( $authorizationService ) {
                                return $authorizationService->isGranted( 'rooms.update', $object );
                            }
                        ],
                        [
                            'name' => 'delete',
                            'icon' => 'fa fa-trash',
                            'tooltip' => 'Delete the room',
                            'class' => 'btn btn-danger btn-xs',
                            'htmlOptions' => [
                                'data-action' => 'room-delete'
                            ],
                            'visible' => function( $object ) use ( $authorizationService ) {
                                return $authorizationService->isGranted( 'rooms.delete', $object );
                            }
                        ]
                    ]
                ]
            )
        );

        $this->setShowColumnsFilter( true );
        $this->setRoute( [ 'route' => 'rooms' ] );
        $this->setHtmlOptions( [ 'id' => 'rooms-data-table' ] );
        $this->setWrapperHtmlOptions( [ 'id' => 'rooms-data-table-wrapper' ] );
        $this->setJavaScriptHandler( $this->getScriptHandler() );
        $this->setJavaScriptFile( '/js/modalWindow.js' );
    }

    protected function getScriptHandler()
    {
        ob_start();
        include __DIR__ . '/../../../data/js/rooms.datatable.js';
        $script = ob_get_contents();
        ob_end_clean();

        return $script;
    }

}
