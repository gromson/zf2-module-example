<?php

namespace Institution\DataTable;

use DataTable\Data\Table;
use DataTable\Data\Column;
use DataTable\Data\ColumnButton;
use ZfcRbac\Service\AuthorizationServiceInterface;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\I18n\Translator\TranslatorInterface;

class SubjectsDataTable extends Table
{

    public function __construct( AuthorizationServiceInterface $authorizationService, TranslatorInterface $translator, Url $url, $data = null )
    {
        if ( $data ) {
            $this->setData( $data );
        }

        $this->addColumn( new Column( [
            'name' => 'title',
            'title' => 'Title',
//			'value' => function($object) use ($url) {
//                return sprintf( '<a href="%2$s" class="modal-view">%1$s</a>', $object->title, $url->fromRoute( 'subjects', ['action' => 'view', 'id' => $object->id ] ) );
//			}
        ] ) );

        $this->addColumn( new Column( [
            'name' => 'title_short',
            'title' => 'Short Title',
        ] ) );

        $this->addColumn( new Column( [
            'name' => 'level',
            'title' => 'Level',
        ] ) );

        $this->addColumn( new Column( [
            'name' => 'programs',
            'title' => 'Traning programs',
            'value' => function($object) use ($translator) {
                $html = '<ul>';

                if ( is_array( $object->programs ) ) {
                    foreach ( $object->programs as $program ) {
                        $html .= '<li>' . $program->title;

                        if ( $program->duration ) {
                            $html .= ' (' . $program->duration . ' ' . $translator->translate( 'hr.', 'subjects' ) . ')';
                        }

                        if ( $program->authors ) {
                            $html .= ' <em>' . $program->authors . '</em>';
                        }

                        $html .= '</li>';
                    }
                }

                return $html . '</ul>';
            }
        ] ) );

        $this->addColumn( new ColumnButton( [
            'name' => 'actions',
            'buttons' => [
                [
                    'name' => 'edit',
                    'icon' => 'fa fa-pencil',
                    'tooltip' => 'Edit the subject',
                    'class' => 'btn btn-primary btn-xs',
                    'htmlOptions' => [
                        'data-action' => 'subject-edit'
                    ],
                    'visible' => function($object) use ($authorizationService) {
						return $authorizationService->isGranted( 'subjects.update', $object );
					}
                ],
                [
                    'name' => 'delete',
                    'icon' => 'fa fa-trash',
                    'tooltip' => 'Delete the subject',
                    'class' => 'btn btn-danger btn-xs',
                    'htmlOptions' => [
                        'data-action' => 'subject-delete'
                    ],
                    'visible' => function($object) use ($authorizationService) {
						return $authorizationService->isGranted( 'subjects.delete', $object );
					}
				],
				[
					'name' => 'deactivate',
					'icon' => 'fa fa-calendar-times-o',
                    'tooltip' => 'Deactivate the subject for the current academic year',
					'class' => 'btn btn-warning btn-xs',
					'htmlOptions' => [
						'data-action' => 'subject-deactivate'
					],
					'visible' => function($object) use ($authorizationService) {
						return $authorizationService->isGranted( 'subjects.deactivate', $object );
					}
				]
			]
        ] ) );

        $this->setShowColumnsFilter( true );
        $this->setRoute( ['route' => 'subjects' ] );
        $this->setHtmlOptions( ['id' => 'subjects-data-table' ] );
        $this->setWrapperHtmlOptions( ['id' => 'subjects-data-table-wrapper' ] );
        $this->setJavaScriptHandler( $this->getScriptHandler() );
    }

    protected function getScriptHandler()
    {
        ob_start();
        include __DIR__ . '/../../../data/js/subjects.datatable.js';
        $script = ob_get_contents();
        ob_end_clean();

        return $script;
    }

}
