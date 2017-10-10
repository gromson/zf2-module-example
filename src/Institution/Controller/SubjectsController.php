<?php

namespace Institution\Controller;

use Application\Controller\AbstractActionController;
use Application\Service\DataTablePluginManager;
use Application\Service\FormPluginManager;
use Authorization\Identity\Identity;
use Institution\Model\SubjectSearch;
use Zend\Debug\Debug;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Institution\Service\SubjectsServiceInterface;
use Institution\Model\Subject;
use Zend\Json\Json;
use Application\Service\Exception\ServiceException;
use ZfcRbac\Exception\UnauthorizedException;

class SubjectsController extends AbstractActionController
{

    /**
     *
     * @var \Institution\Service\SubjectsServiceInterface
     */
    protected $subjectsService;

    /**
     * @var DataTablePluginManager
     */
    protected $dataTablePluginManager;

    /**
     * @var FormPluginManager
     */
    protected $formPluginManager;

    /**
     * @var Identity
     */
    protected $authIdentity;

    /**
     * SubjectsController constructor.
     *
     * @param SubjectsServiceInterface $subjectsService
     * @param DataTablePluginManager   $dataTablePluginManager
     * @param FormPluginManager        $formPluginManager
     * @param Identity                 $authIdentity
     */
    public function __construct(
        SubjectsServiceInterface $subjectsService,
        DataTablePluginManager $dataTablePluginManager,
        FormPluginManager $formPluginManager,
        Identity $authIdentity
    )
    {
        $this->subjectsService = $subjectsService;
        $this->dataTablePluginManager = $dataTablePluginManager;
        $this->formPluginManager = $formPluginManager;
        $this->authIdentity = $authIdentity;
    }

    public function onDispatch( MvcEvent $e )
    {
        $this->setTranslatorTextDomain( 'subjects' );

        return parent::onDispatch( $e );
    }

    public function listAction()
    {
        $this->PageMeta()->setTranslatorTextDomain( 'subjects' )->setPageTitle( 'Subjects' );
        $this->PageTitle( 'Subjects', 'subjects' );

        $filter = [
            'title' => $this->params()->fromQuery( 'title' ),
            'titleShort' => $this->params()->fromQuery( 'title_short' ),
            'level' => $this->params()->fromQuery( 'level' ),
            'programs' => $this->params()->fromQuery( 'programs' ),
        ];

        $search = new SubjectSearch( array_filter( $filter ) );

        $list = $this->subjectsService->setSearchModel( $search )->fetch( true );

        $rpp = (int)$this->params()->fromQuery( 'rpp', $this->getRpp() );

        $list->setCurrentPageNumber( (int)$this->params()->fromQuery( 'page', 1 ) );
        $list->setItemCountPerPage( $rpp );

        if ( isset( $_GET[ 'rpp' ] ) ) {
            $this->Cookie()->set( 'rpp', $rpp );
        }

        $this->subjectsService->appendPrograms( $list );

        $dataTable = $this->dataTablePluginManager->get(
            'subjectsdatatable',
            [
                'data' => $list,
                'url' => $this->url()
            ]
        );

        return new ViewModel(
            [
                'dataTable' => $dataTable
            ]
        );
    }

    public function viewAction()
    {
        if ( !$id = $this->params()->fromRoute( 'id' ) ) {
            return $this->error404();
        }

        $subject = $this->subjectsService->setWithSubjectPrograms( true )->get( $id );

        if ( !( $subject instanceof Subject ) ) {
            return $this->error404();
        }

        if ( isset( $_GET[ 'ajax' ] ) ) {
            $result = [
                'subject' => $subject->getArrayCopy()
            ];

            foreach ( $subject->subjectProgram as $program ) {
                $result[ 'programs' ][] = $program->getArrayCopy();
            }

            $response = $this->getResponse();
            $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
            $response->getHeaders()->addHeader( $header );
            $response->setContent( Json::encode( $result ) );

            return $response;
        } else {
            $this->PageMeta()->setTranslatorTextDomain( 'subjects' )->setPageTitle( 'View a subject' );
            $this->PageTitle( 'View a subject', 'subjects' );

            return new ViewModel( [ 'subject' => $subject ] );
        }
    }

    public function createAction()
    {
        $this->PageMeta()->setTranslatorTextDomain( 'subjects' )->setPageTitle( 'Add new subject' );
        $this->PageTitle( 'Add new subject', 'subjects' );

        $form = $this->formPluginManager->get( 'subjectform', [ 'save_button_text' => 'Create' ] );

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $subject = new Subject();
            $form->setData( $request->getPost() );

            if ( $form->isValid() ) {
                $subject->exchangeArray( $form->getData(), true );
                $subject->accounts_id = $this->authIdentity->getAccountId();

                try {
                    $subject = $this->subjectsService->save( $subject );

                    $this->flashMessenger()->addSuccessMessage( 'New subject has been created!' );
                    $this->redirect()->toRoute( 'subjects', [ 'action' => 'list' ] );
                } catch ( ServiceException $ex ) {
                    $this->flashMessenger()->addErrorMessage( $ex->getMessage() );
                }
            }
        }

        return new ViewModel( [ 'form' => $form ] );
    }

    public function updateAction()
    {
        if ( !$id = $this->params()->fromRoute( 'id' ) ) {
            return $this->error404();
        }

        $this->PageMeta()->setTranslatorTextDomain( 'subjects' )->setPageTitle( 'Update a subject' );
        $this->PageTitle( 'Update a subject', 'subjects' );

        $subject = $this->subjectsService->getWithPrograms( true )->get( $id, true, 'subjects.update' );

        $form = $this->formPluginManager->get( 'subjectform' );
        $form->setData( $subject->getArrayCopy( true ) );

        if ( $subject->ancestor_id ) {
            $areas = $this->subjectsService->fetchAreasForDropDown();
            $form->get( 'subject_areas_id' )->setValueOptions(
                [
                    $subject->subject_areas_id => $areas[ $subject->subject_areas_id ]
                ]
            );
            $form->get( 'subject_areas_id' )->setAttribute( 'readonly', 'readonly' );
            $form->get( 'title' )->setAttribute( 'readonly', 'readonly' );
            $form->get( 'title_short' )->setAttribute( 'readonly', 'readonly' );
            $form->get( 'level' )->setAttribute( 'readonly', 'readonly' );
        }

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $form->setData( $request->getPost() );

            if ( $form->isValid() ) {
                $subject->exchangeArray( $form->getData(), false );

                try {
                    $this->subjectsService->save( $subject );

                    $this->flashMessenger()->addSuccessMessage( 'A subject has been updated!' );
                    $this->redirect()->toRoute( 'subjects', [ 'action' => 'list' ] );
                } catch ( ServiceException $ex ) {
                    $this->flashMessenger()->addErrorMessage( $ex->getMessage() );
                }
            }
        }

        return new ViewModel( [ 'form' => $form ] );
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
        $response->getHeaders()->addHeader( $header );

        if ( $request->isPost() ) {
            if ( $id = $this->params()->fromPost( 'id' ) ) {
                $result = [ 'status' => $this->subjectsService->delete( $id ) ];
            } else {
                $result = [ 'status' => false ];
            }
        } else {
            return $this->httpStatusCode( 403 );
        }

        $response->setContent( Json::encode( $result ) );

        return $response;
    }

    public function deactivateAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
        $response->getHeaders()->addHeader( $header );

        if ( $request->isPost() ) {
            if ( $id = $this->params()->fromPost( 'id' ) ) {
                $result = [ 'status' => $this->subjectsService->deactivate( $id ) ];
            } else {
                $result = [ 'status' => false ];
            }
        } else {
            return $this->httpStatusCode( 403 );
        }

        $response->setContent( Json::encode( $result ) );

        return $response;
    }

    public function deleteProgramAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
        $response->getHeaders()->addHeader( $header );

        if ( $request->isPost() ) {
            if ( $id = $this->params()->fromPost( 'id' ) ) {
                $subject_id = $this->params()->fromPost( 'subject_id' );

                $subject = $this->subjectsService->get( $subject_id );

                try {
                    $result = [ 'status' => $this->subjectsService->deleteProgram( $id, $subject ) ];
                } catch ( UnauthorizedException $ex ) {
                    $result = [ 'status' => false ];
                }
            } else {
                $result = [ 'status' => true ];
            }
        } else {
            $result = [ 'status' => false ];
//			return $this->httpStatusCode( 403 );
        }

        $response->setContent( Json::encode( $result ) );

        return $response;
    }

}
