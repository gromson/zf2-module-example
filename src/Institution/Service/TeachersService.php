<?php

namespace Institution\Service;

use Institution\Mapper\TeachersMapperInterface;
use Institution\Model\TeacherSearch;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Exception\UnauthorizedException;
use Institution\Model\Teacher;
use Institution\Model\Subject;
use Users\Model\User;
use Authorization\Identity\Identity;
use Application\Exception\RuntimeException;
use Application\Service\Exception\ServiceException;
use Users\Service\UsersServiceInterface;

class TeachersService implements TeachersServiceInterface
{

    /**
     *
     * @var Identity
     */
    protected $identity;

    /**
     *
     * @var \Institution\Mapper\TeachersMapperInterface
     */
    protected $mapper;

    /**
     *
     * @var UsersServiceInterface
     */
    protected $usersService;

    /**
     *
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    public function __construct(
        Identity $identity,
        TeachersMapperInterface $mapper,
        UsersServiceInterface $usersServive,
        AuthorizationServiceInterface $authorizationService
    )
    {
        $this->identity = $identity;
        $this->mapper = $mapper;
        $this->usersService = $usersServive;
        $this->authorizationService = $authorizationService;
    }

    /**
     *
     * @param boolean $paginated
     *
     * @return Teacher[]|\Zend\Paginator\Paginator
     */
    public function fetch($paginated = false)
    {
        return $this->mapper->fetch($this->identity->getAccountId(), $paginated);
    }

    /**
     *
     * @param array|\Iterator|\IteratorAggregate|Teacher $teachers
     *
     * @return \Institution\Service\TeachersService
     */
    public function appendSubjects(&$teachers)
    {
        $this->mapper->appendSubjects($teachers);

        return $this;
    }

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Teacher
     * @throws UnauthorizedException
     */
    public function get($id, $secured = true, $permission = 'teachers.view')
    {
        $teacher = $this->mapper->get($id);

        if ($secured) {
            if ($this->authorizationService->isGranted($permission, $teacher)) {
                return $teacher;
            } else {
                throw new UnauthorizedException();
            }
        }

        return $teacher;
    }

    /**
     *
     * @param Teacher $teacher
     *
     * @return false|Teacher
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    public function save(Teacher $teacher)
    {
        $this->beginTransaction();

        if ($teacher->has_access === true && !$teacher->users_id && $this->authorizationService->isGranted(
                'users.create'
            )
        ) {
            $user = new User();
            $user->exchangeArray(
                [
                    'accounts_id' => $teacher->accounts_id,
                    'users_roles_code' => $teacher->users_roles_code,
                    'email' => $teacher->email,
                    'phone' => $teacher->phone,
                    'firstname' => $teacher->firstname,
                    'lastname' => $teacher->lastname,
                    'middlename' => $teacher->middlename
                ],
                true
            );

            $user = $this->usersService->save($user);

            if (($user instanceof User) === false) {
                throw new ServiceException('Error occured while trying to create a user!');
            }

            $teacher->users_id = $user->id;
        } elseif ((bool) $teacher->has_access === false) {
            $teacher->users_id = null;
        }

        if ($teacher->getIsNewRecord() && $this->authorizationService->isGranted('teachers.create')) {
            $teacher = $this->mapper->create($teacher);
        } elseif (!$teacher->getIsNewRecord() && $this->authorizationService->isGranted('teachers.update', $teacher)) {
            $teacher = $this->mapper->update($teacher);
        } else {
            throw new UnauthorizedException();
        }

        if ($teacher) {
            $this->commit();

            return $teacher;
        } else {
            $this->rollback();
            throw new ServiceException('Error occured while trying to save a teacher!');
        }
    }

    /**
     *
     * @param Teacher|int $teacher
     *
     * @return boolean
     * @throws \Exception
     * @throws UnauthorizedException
     */
    public function delete($teacher)
    {
        $id = is_numeric($teacher) ? (int) $teacher : $teacher->id;

        if (($teacher instanceof Teacher) === false) {
            $teacher = $this->get($id, false);
        }

        if ($this->authorizationService->isGranted('teachers.delete', $teacher)) {
            if (!is_numeric($teacher) && !($teacher instanceof Teacher)) {
                throw new RuntimeException(
                    'Wrong 1 argument given in TeachersService::delete(), argument must be an integer or an instance of \Institution\Model\Teacher!'
                );
            }

            return $this->mapper->delete($id);
        } else {
            throw new UnauthorizedException();
        }
    }

    public function addVacancy(Subject $subject)
    {
        if ($this->authorizationService->isGranted('curriculum.management')) {
            return $this->mapper->addVacancy($subject);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     * @param TeacherSearch $search
     *
     * @return TeachersService
     */
    public function setSearchModel(TeacherSearch $search)
    {
        $this->mapper->setSearchModel($search);

        return $this;
    }

    protected function beginTransaction()
    {
        if (is_callable([$this->mapper, 'beginTransaction'])) {
            $this->mapper->beginTransaction();
        }
    }

    protected function commit()
    {
        if (is_callable([$this->mapper, 'commit'])) {
            $this->mapper->commit();
        }
    }

    protected function rollback()
    {
        if (is_callable([$this->mapper, 'rollback'])) {
            $this->mapper->rollback();
        }
    }

}
