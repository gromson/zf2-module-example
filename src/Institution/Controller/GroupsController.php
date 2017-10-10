<?php

namespace Institution\Controller;

use Application\Controller\AbstractActionController;
use Application\Service\FormPluginManager;
use Authorization\Identity\Identity;
use Zend\Debug\Debug;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Institution\Service\GroupsServiceInterface;
use Application\Service\Exception\ServiceException;
use Institution\Model\Group;
use Institution\Iterator\GroupIterator;

class GroupsController extends AbstractActionController
{

    /**
     *
     * @var GroupsServiceInterface
     */
    protected $groupService;

    /**
     * @var FormPluginManager
     */
    protected $formPluginManager;

    /**
     * @var Identity
     */
    protected $identity;

    /**
     * GroupsController constructor.
     *
     * @param GroupsServiceInterface $groupsService
     * @param FormPluginManager      $formPluginManager
     * @param Identity               $identity
     */
    public function __construct(
        GroupsServiceInterface $groupsService,
        FormPluginManager $formPluginManager,
        Identity $identity
    )
    {
        $this->groupService = $groupsService;
        $this->formPluginManager = $formPluginManager;
        $this->identity = $identity;
    }

    public function onDispatch( MvcEvent $e )
    {
        $this->setTranslatorTextDomain( 'groups' );

        return parent::onDispatch( $e );
    }

    public function indexAction()
    {
        $this->PageMeta()->setTranslatorTextDomain( 'groups' )->setPageTitle( 'Groups' );
        $this->PageTitle( 'Groups', 'groups' );

        $formCategory = $this->params()->fromRoute( 'form_category', GroupsServiceInterface::ELEMENTARY_SCHOOL );
        $year = $this->identity->getWorkingYear();

        $groups = $this->groupService->fetch( $year, false );

        $formData = [
            'groups' => $this->getGroupsAsArray( $groups, $formCategory )
        ];

        $form = $this->formPluginManager->get( 'groupform' );
        $form->setData( $formData );
        $form->get( 'year' )->setValue( $year );
//        $form->get( 'year' )->setOptions( ['value_options' => $form->getYearsArray() ] );

        $request = $this->getRequest();

        if ( $request->isPost() ) {
            $form->setData( $request->getPost() );

            if ( $form->isValid() ) {
                try {
                    $form->saveGroups( $this->groupService, $this->identity->getAccountId() );

                    $this->flashMessenger()->addSuccessMessage( 'Groups have been saved!' );

                    if ( $this->getSessionValue( 'tour' ) ) {
                        $this->redirect()->toRoute(
                            'groups',
                            [ 'action' => 'index', 'form_category' => $formCategory ],
                            [ 'query' => [ 'tour_step' => 4 ] ]
                        );
                    } else {
                        $this->redirect()->toRoute(
                            'groups',
                            [ 'action' => 'index', 'form_category' => $formCategory ]
                        );
                    }
                } catch ( ServiceException $ex ) {
                    $this->flashMessenger()->addErrorMessage( $ex->getMessage() );
                }
            } else {
                if ( APPLICATION_ENVIRONMENT == ENVIRONMENT_DEVELOPMENT ) {
                    \Zend\Debug\Debug::dump( $form->getInputFilter()->getMessages() );
                }
            }
        }

        return new ViewModel(
            [
                'form' => $form,
                'currentFormCategory' => $formCategory,
                'isTour' => $this->getIsTour(),
                'tourStep' => $this->params()->fromQuery( 'tour_step', 0 )
            ]
        );
    }

    /**
     *
     * @param GroupIterator|Group[] $groups
     * @param string                $currentFormCategory
     *
     * @return array
     */
    protected function getGroupsAsArray( $groups, string $currentFormCategory = null )
    {
        $result = [];

        foreach ( $groups as $g ) {
            if ( $g->getActiveGrade() ) {
                $group = $g->getArrayCopy( true );

                $groupsService = $this->groupService;

                if ( $currentFormCategory !== null && $currentFormCategory === $groupsService::getSchoolGradeCategory(
                        $g->getActiveGrade()->level
                    )
                ) {
                    $result[] = $group;
                } elseif ( $currentFormCategory === null ) {
                    $result[] = $group;
                }
            }
        }

        return array_values( array_filter( $result ) );
    }

}
