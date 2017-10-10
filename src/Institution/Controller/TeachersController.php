<?php

namespace Institution\Controller;

use Application\Controller\AbstractActionController;
use Application\Service\DataTablePluginManager;
use Application\Service\FormPluginManager;
use Authorization\Identity\Identity;
use Institution\Model\TeacherSearch;
use Institution\Service\TeachersDismissServiceInterface;
use Zend\Debug\Debug;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Institution\Service\TeachersServiceInterface;
use Institution\Service\SubjectsServiceInterface;
use Institution\Model\Teacher;
use Zend\Json\Json;
use Application\Service\Exception\ServiceException;
use ZfcRbac\Exception\UnauthorizedException;

class TeachersController extends AbstractActionController
{

    /**
     *
     * @var \Institution\Service\TeachersServiceInterface
     */
    protected $teachersService;

    /**
     * @var TeachersDismissServiceInterface
     */
    protected $teachersDismissService;

    /**
     *
     * @var SubjectsServiceInterface
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
    protected $identity;

    /**
     * TeachersController constructor.
     *
     * @param TeachersServiceInterface        $teachersService
     * @param TeachersDismissServiceInterface $teachersDismissService
     * @param SubjectsServiceInterface        $subjectsService
     * @param DataTablePluginManager          $dataTablePluginManager
     * @param FormPluginManager               $formPluginManager
     * @param Identity                        $identity
     */
    public function __construct(
        TeachersServiceInterface $teachersService,
        TeachersDismissServiceInterface $teachersDismissService,
        SubjectsServiceInterface $subjectsService,
        DataTablePluginManager $dataTablePluginManager,
        FormPluginManager $formPluginManager,
        Identity $identity
    )
    {
        $this->teachersService = $teachersService;
        $this->teachersDismissService = $teachersDismissService;
        $this->subjectsService = $subjectsService;
        $this->dataTablePluginManager = $dataTablePluginManager;
        $this->formPluginManager = $formPluginManager;
        $this->identity = $identity;
    }

    public function onDispatch(MvcEvent $e)
    {
        $this->setTranslatorTextDomain('teachers');

        return parent::onDispatch($e);
    }

    public function listAction()
    {
        $this->PageMeta()->setTranslatorTextDomain('teachers')->setPageTitle('Teachers');
        $this->PageTitle('Teachers', 'teachers');

        $filter = [
            'fullname' => $this->params()->fromQuery('fullname'),
            'subjects' => $this->params()->fromQuery('subjects'),
            'email' => $this->params()->fromQuery('email'),
            'phone' => $this->params()->fromQuery('phone'),
            'vacancy' => $this->params()->fromQuery('vacancy'),
            'dismissed' => $this->params()->fromQuery('dismissed', 0),
        ];

        $searchModel = new TeacherSearch(array_filter($filter, function($v){ return $v !== null && $v !== ''; }));

        $list = $this->teachersService->setSearchModel($searchModel)->fetch(true);

        $list->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $list->setItemCountPerPage((int) $this->params()->fromQuery('rpp', $this->getRpp()));

        $this->teachersService->appendSubjects($list);

        $dataTable = $this->dataTablePluginManager->get(
            'teachersdatatable',
            [
                'data' => $list,
                'url' => $this->url()
            ]
        );

        $result = new ViewModel(
            [
                'dataTable' => $dataTable,
                'isTour' => $this->getIsTour(),
                'tourStep' => $this->params()->fromQuery('tour_step', 0)
            ]
        );

        $result->setTerminal(isset($_GET['ajax']));

        return $result;
    }

    public function viewAction()
    {
        if (!$id = $this->params()->fromRoute('id')) {
            return $this->error404();
        }

        $this->PageMeta()->setTranslatorTextDomain('teachers')->setPageTitle('View a teacher');
        $this->PageTitle('View a teacher', 'teachers');

        $teacher = $this->teachersService->get($id);

        if (!($teacher instanceof Teacher)) {
            return $this->error404();
        }

        return new ViewModel(['teacher' => $teacher,]);
    }

    public function createAction()
    {
        $this->PageMeta()->setTranslatorTextDomain('teachers')->setPageTitle('Add a new teacher');
        $this->PageTitle('Add a new teacher', 'teachers');

        $form = $this->formPluginManager->get('teacherform', ['save_button_text' => 'Create']);
        $form->get('users_roles_code')->setValue('teacher');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $teacher = new Teacher();
                $teacher->exchangeArray($form->getData(), true);
                $teacher->accounts_id = $this->identity->getAccountId();

                try {
                    $teacher = $this->teachersService->save($teacher);

                    $this->flashMessenger()->addSuccessMessage('New teacher has been created!');

                    if ($this->getSessionValue('tour')) {
                        $this->redirect()->toRoute('teachers', ['action' => 'list'], ['query' => ['tour_step' => 2]]);
                    } else {
                        $this->redirect()->toRoute('teachers', ['action' => 'list']);
                    }
                } catch (ServiceException $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            }
        }

        return new ViewModel(
            [
                'form' => $form,
                'isTour' => $this->getIsTour()
            ]
        );
    }

    public function updateAction()
    {
        if (!$id = $this->params()->fromRoute('id')) {
            return $this->error404();
        }

        $teacher = $this->teachersService->get($id, true, 'teachers.update');

        $this->PageMeta()->setTranslatorTextDomain('teachers');
        if (!$teacher->vacancy) {
            $this->PageMeta()->setPageTitle('Update a teacher');
            $this->PageTitle('Update a teacher', 'teachers');
        } else {
            $this->PageMeta()->setPageTitle('Fill the vacancy');
            $this->PageTitle('Fill the vacancy', 'teachers');
        }

        $form = $this->formPluginManager->get('teacherform', ['acceptable_user_id' => $teacher->users_id]);
        $form->setData($teacher->getArrayCopy(true));

        $form->get('subjects_id')->setValue($teacher->subjects_id);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $result = [];

            $form->setData($request->getPost());

            if ($form->isValid()) {
                $teacher->exchangeArray($form->getData(), false);

                try {
                    $teacher = $this->teachersService->save($teacher);

                    $this->flashMessenger()->addSuccessMessage('A teacher has been updated!');
                    $this->redirect()->toRoute('teachers', ['action' => 'list']);
                } catch (ServiceException $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            }
        }

        return new ViewModel(['form' => $form, 'vacancy' => $teacher->vacancy]);
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $header = new \Zend\Http\Header\ContentType('application/json; charset=utf-8');
        $response->getHeaders()->addHeader($header);

        if ($request->isPost()) {
            if ($id = $this->params()->fromPost('id')) {
                $result = ['status' => $this->teachersService->delete($id)];
            } else {
                $result = ['status' => false];
            }
        } else {
            return $this->httpStatusCode(403);
        }

        $response->setContent(Json::encode($result));

        return $response;
    }

    public function addVacancyAction()
    {
        $request = $this->getRequest();
        $result = ['status' => false];

        if ($request->isPost()) {
            $subjectId = $this->params()->fromPost('subject_id');

            if (!$subjectId) {
                return $this->error404();
            }

            $subject = $this->subjectsService->get($subjectId, true, 'curriculum.management');
            if ($teacher = $this->teachersService->addVacancy($subject)) {
                $result['teacher'] = $teacher->getArrayCopy();
                $result['status'] = true;
            }
        } else {
            return $this->error404();
        }

        return $this->returnJson($result);
    }

    public function dismissAction()
    {
        $request = $this->getRequest();
        $result = ['status' => false];

        if ($request->isPost()) {
            if (!$id = $this->params()->fromPost('id')) {
                return $this->error404();
            }

            $addVacancy = (bool) $this->params()->fromPost('add_vacancy', false);

            $teacher = $this->teachersService->get($id, true, 'teachers.dismiss');

            if ($teacher = $this->teachersDismissService->dismiss($teacher, $addVacancy)) {
                $result['teacher'] = $teacher->getArrayCopy();
                $result['status'] = true;
            }
        } else {
            return $this->error404();
        }

        return $this->returnJson($result);
    }
}
