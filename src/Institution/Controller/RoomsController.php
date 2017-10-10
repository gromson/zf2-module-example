<?php

namespace Institution\Controller;

use Application\Controller\AbstractActionController;
use Application\Service\DataTablePluginManager;
use Application\Service\FormPluginManager;
use Authorization\Identity\Identity;
use Institution\Model\RoomSearch;
use Zend\Debug\Debug;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Institution\Service\RoomsServiceInterface;
use Institution\Model\Room;
use Zend\Json\Json;
use Application\Service\Exception\ServiceException;
use Zend\I18n\Translator\TranslatorInterface;

class RoomsController extends AbstractActionController
{

    /**
     *
     * @var \Institution\Service\RoomsServiceInterface
     */
    protected $roomsService;

    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;

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
    protected $identity;

    /**
     * RoomsController constructor.
     *
     * @param RoomsServiceInterface  $roomsService
     * @param TranslatorInterface    $translator
     * @param DataTablePluginManager $dataTablePluginManager
     * @param FormPluginManager      $formPluginManager
     * @param Identity               $identity
     */
    public function __construct(
        RoomsServiceInterface $roomsService,
        TranslatorInterface $translator,
        DataTablePluginManager $dataTablePluginManager,
        FormPluginManager $formPluginManager,
        Identity $identity
    )
    {
        $this->roomsService = $roomsService;
        $this->translator = $translator;
        $this->dataTablePluginManager = $dataTablePluginManager;
        $this->formPluginManager = $formPluginManager;
        $this->identity = $identity;
    }

    public function onDispatch( MvcEvent $e )
    {
        $this->setTranslatorTextDomain( 'rooms' );

        return parent::onDispatch( $e );
    }

    public function listAction()
    {
        $this->PageMeta()->setTranslatorTextDomain( 'rooms' )->setPageTitle( 'Rooms' );
        $this->PageTitle( 'Rooms', 'rooms' );

        $filter = [
            'number' => $this->params()->fromQuery( 'number' ),
            'roomsCategoriesId' => $this->params()->fromQuery( 'rooms_categories_id' ),
            'subjects' => $this->params()->fromQuery( 'subjects' ),
            'capacity' => $this->params()->fromQuery( 'capacity' ),
            'comment' => $this->params()->fromQuery( 'comment' ),
        ];

        $searchModel = new RoomSearch( array_filter( $filter ) );

        $list = $this->roomsService
            ->pullWithCategories()
            ->setSearchModel( $searchModel )
            ->fetch( true );

        $list->setCurrentPageNumber( (int) $this->params()->fromQuery( 'page', 1 ) );
        $list->setItemCountPerPage( (int) $this->params()->fromQuery( 'rpp', $this->getRpp() ) );

        $this->roomsService->appendSubjects( $list );

        $dataTable = $this->dataTablePluginManager->get( 'roomsdatatable', [ 'data' => $list, 'url' => $this->url() ] );

        $result = new ViewModel(
            [
                'dataTable' => $dataTable,
                'isTour' => $this->getIsTour(),
                'tourStep' => $this->params()->fromQuery( 'tour_step', 0 )
            ]
        );

        if ( isset( $_GET['ajax'] ) === true ) {
            $result->setTerminal( true );
        }

        return $result;
    }

    public function viewAction()
    {
        if ( !$id = $this->params()->fromRoute( 'id' ) ) {
            return $this->error404();
        }

        $this->PageMeta()->setTranslatorTextDomain( 'rooms' )->setPageTitle( 'View a room' );
        $this->PageTitle( 'View a room', 'rooms' );

        $room = $this->roomsService
            ->pullWithCategories()
            ->pullWithSubjects()
            ->get( $id );

        if ( !( $room instanceof Room ) ) {
            return $this->error404();
        }

        return new ViewModel( [ 'room' => $room ] );
    }

    public function createAction()
    {
        $this->PageMeta()->setTranslatorTextDomain( 'rooms' )->setPageTitle( 'Add new room' );
        $this->PageTitle( 'Add new room', 'rooms' );

        $form = $this->formPluginManager->get( 'roomform', [ 'save_button_text' => 'Create' ] );

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $result = [ ];

            $room = new Room();
            $form->setData( $request->getPost() );

            if ( $form->isValid() ) {
                $room->exchangeArray( $form->getData(), true );
                $room->accounts_id = $this->identity->getAccountId();

                try {
                    $room = $this->roomsService->save( $room );
                    $result = [ 'status' => true, 'id' => $room->id, 'message' => $this->translator->translate(
                        'New room has been created!',
                        'rooms'
                    ) ];
                } catch ( ServiceException $ex ) {
                    $result = [ 'status' => false, 'message' => $this->translator->translate(
                        $ex->getMessage(),
                        'rooms'
                    ) ];
                }
            } else {
                $result = [ 'status' => false, 'message' => $this->TranslateFormMessages(
                    $form->getMessages(),
                    'rooms'
                ) ];
            }

            $response = $this->getResponse();
            $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
            $response->getHeaders()->addHeader( $header );
            $response->setStatusCode( 200 )
                ->setContent( Json::encode( $result ) );

            return $response;
        }

        return ( new ViewModel( [ 'form' => $form ] ) )
            ->setTerminal( true );
    }

    public function updateAction()
    {
        if ( !$id = $this->params()->fromRoute( 'id' ) ) {
            return $this->error404();
        }

        $this->PageMeta()->setTranslatorTextDomain( 'rooms' )->setPageTitle( 'Update a room' );
        $this->PageTitle( 'Update a room', 'rooms' );

        $room = $this->roomsService
            ->pullWithCategories()
            ->pullWithSubjects()
            ->get( $id, true, 'rooms.update' );

        $form = $this->formPluginManager->get( 'roomform' );
        $form->setData( $room->getArrayCopy( true ) );

        $form->get( 'subjects_id' )->setValue( $room->subjects_id );

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $result = [ ];

            $postData = $request->getPost();

            if ( !isset( $postData['subjects_id'] ) ) {
                $postData['subjects_id'] = [ ];
            }

            $form->setData( $postData );

            if ( $form->isValid() ) {
                $room->exchangeArray( $form->getData(), false );

                try {
                    $room = $this->roomsService->save( $room );
                    $result = [ 'status' => true, 'id' => $room->id, 'message' => $this->translator->translate(
                        'A room has been updated!',
                        'rooms'
                    ) ];
                } catch ( ServiceException $ex ) {
                    $result = [ 'status' => false, 'message' => $this->translator->translate(
                        $ex->getMessage(),
                        'rooms'
                    ) ];
                }
            } else {
                $result = [ 'status' => false, 'message' => $this->TranslateFormMessages(
                    $form->getMessages(),
                    'rooms'
                ) ];
            }

            $response = $this->getResponse();
            $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
            $response->getHeaders()->addHeader( $header );
            $response->setStatusCode( 200 )
                ->setContent( Json::encode( $result ) );

            return $response;
        }

        return ( new ViewModel( [ 'form' => $form ] ) )
            ->setTerminal( true );
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $header = new \Zend\Http\Header\ContentType( 'application/json; charset=utf-8' );
        $response->getHeaders()->addHeader( $header );

        if ( $request->isPost() ) {
            if ( $id = $this->params()->fromPost( 'id' ) ) {
                $result = [ 'status' => $this->roomsService->delete( $id ) ];
            } else {
                $result = [ 'status' => false ];
            }
        } else {
            return $this->httpStatusCode( 403 );
        }

        $response->setContent( Json::encode( $result ) );

        return $response;
    }

}
