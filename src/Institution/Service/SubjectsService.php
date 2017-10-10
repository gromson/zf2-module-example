<?php

namespace Institution\Service;

use Application\Model\SearchModelInterface;
use Institution\Iterator\RoomIterator;
use Institution\Iterator\SubjectIterator;
use Institution\Iterator\SubjectsProgramIterator;
use Institution\Mapper\SubjectsMapperInterface;
use Institution\Mapper\SubjectsProgramsMapperInterface;
use Institution\Model\SubjectSearch;
use Symfony\Component\Yaml\Dumper;
use Zend\Debug\Debug;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Exception\UnauthorizedException;
use Institution\Model\Subject;
use Institution\Model\SubjectProgram;
use Authorization\Identity\Identity;
use Application\Exception\RuntimeException;
use Application\Service\Exception\ServiceException;

class SubjectsService implements SubjectsServiceInterface
{
    const ORDER_BY_SUBJECT_AREA = 1;
    const ORDER_BY_LEVEL = 2;

    /**
     *
     * @var Identity
     */
    protected $identity;

    /**
     *
     * @var \Institution\Mapper\SubjectsMapperInterface
     */
    protected $mapper;

    /**
     *
     * @var SubjectsProgramsMapperInterface
     */
    protected $programMapper;

    /**
     *
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * @var bool
     */
    protected $withPrograms = false;

    /**
     * @var null|SubjectIterator
     */
    protected $cachedSubjects = null;

    public function __construct(
        Identity $identity,
        SubjectsMapperInterface $mapper,
        SubjectsProgramsMapperInterface $programMapper,
        AuthorizationServiceInterface $authorizationService
    )
    {
        $this->identity = $identity;
        $this->mapper = $mapper;
        $this->programMapper = $programMapper;
        $this->authorizationService = $authorizationService;
    }

    /**
     *
     * @param boolean $paginated
     * @param string  $category one of the GroupsServiceInterface::*_SCHOOL constants
     * @param int     $orderBy
     *
     * @return SubjectIterator|\Zend\Paginator\Paginator
     *
     */
    public function fetch(
        $paginated = false,
        string $category = null,
        int $orderBy = self::ORDER_BY_SUBJECT_AREA
    )
    {
        if (!$this->cachedSubjects) {
            $this->cachedSubjects = $this->mapper->fetch(
                $this->identity->getAccountId(),
                $this->identity->getWorkingYear(),
                $paginated,
                $orderBy
            );
        }

        if ($this->withPrograms && !$paginated) {
            $this->appendPrograms($this->cachedSubjects);
        }

        if (!$paginated && $category) {
            $subjects = new SubjectIterator(null);

            $this->cachedSubjects->rewind();

            foreach ($this->cachedSubjects as $subject) {
                if (in_array($category, $subject->school_level)) {
                    $subjects->addItem($this->filterTeachersBySchoolLevel($subject, $category));
                }
            }
        } else {
            $subjects = $this->cachedSubjects;
        }

        return $subjects;
    }

    /**
     * @param array|RoomIterator $roomIds
     *
     * @return \Institution\Iterator\SubjectIterator|array
     */
    public function fetchForRooms($roomIds)
    {
        $ids = [];

        if ($roomIds instanceof RoomIterator) {
            foreach ($roomIds as $room) {
                $ids[] = $room->id;
            }
        } else {
            $ids = $roomIds;
        }

        return $this->mapper->fetchForRooms($ids, $this->identity->getWorkingYear());
    }

    /**
     *
     * @param boolean $withBlankValue whether to show a null value
     * @param string  $blankValueText
     *
     * @return array
     */
    public function fetchForDropDown($withBlankValue = false, $blankValueText = '- none -')
    {
        $list = $this->mapper->fetch($this->identity->getAccountId(), $this->identity->getWorkingYear());

        $resultArray = [];

        if ($withBlankValue) {
            $resultArray[null] = $blankValueText;
        }

        foreach ($list as $subject) {
//            $resultArray[ $subject->id ] = $subject->title;
            $resultArray[] = [
                'value' => $subject->id,
                'label' => $subject->title,
                'attributes' => [
                    'data-elementary-school'
                    => (int) in_array(GroupsServiceInterface::ELEMENTARY_SCHOOL, $subject->school_level)
                ]
            ];
        }

        return $resultArray;
    }

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Subject
     * @throws UnauthorizedException
     */
    public function get($id, $secured = true, $permission = 'subjects.view')
    {
        $subject = $this->mapper->get($id);

        if ($this->withPrograms) {
            $this->appendPrograms($subject);
        }

        if ($secured) {
            if ($this->authorizationService->isGranted($permission, $subject)) {
                return $subject;
            } else {
                throw new UnauthorizedException();
            }
        }

        return $subject;
    }

    /**
     *
     * @param int     $id
     * @param bool    $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     * @param Subject $subject
     *
     * @return SubjectProgram
     * @throws UnauthorizedException
     */
    public function getProgram($id, $secured = true, $permission = 'subjects.programs.view', Subject $subject = null)
    {
        $program = $this->programMapper->get($id);

        if ($secured) {
            if ($this->authorizationService->isGranted($permission, $subject, $program)) {
                return $program;
            } else {
                throw new UnauthorizedException();
            }
        }

        return $program;
    }

    /**
     *
     * @param Subject $subject
     *
     * @return Subject|false
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    public function save(Subject $subject)
    {
        $subjectsPrograms = $subject->programs;

        $this->beginTransaction();

        if ($subject->getIsNewRecord() && $this->authorizationService->isGranted('subjects.create')) {
            $subject = $this->mapper->create($subject, $this->identity->getWorkingYear());
        } elseif (!$subject->getIsNewRecord()
            && $this->authorizationService->isGranted(
                'subjects.update',
                $subject
            )
        ) {
            $subject = $this->mapper->update($subject);
        } else {
            throw new UnauthorizedException();
        }

        if ($subject) {
            foreach ($subjectsPrograms as $key => $sp) {
                if ($sp->isNewRecord()) {
                    $sp->subjects_id = $subject->id;

                    if (!$program = $this->programMapper->create($sp)) {
                        $this->rollback();
                        throw new ServiceException('Error occured white trying to create traning program!');
                    }

                    $subjectsPrograms[$key] = $program;
                } else {
                    if (!$this->programMapper->update($sp)) {
                        $this->rollback();
                        throw new ServiceException('Error occured white trying to update traning program!');
                    }
                }
            }

            $this->commit();

            return $subject->setRelations('subjectProgram', $subjectsPrograms);
        } else {
            $this->rollback();
            throw new ServiceException('Error occured while trying to save a subject!');
        }
    }

    /**
     *
     * @param Subject|int $subject
     *
     * @return boolean
     * @throws \Exception
     * @throws UnauthorizedException
     */
    public function delete($subject)
    {
        $id = is_numeric($subject) ? (int) $subject : $subject->id;

        if (($subject instanceof Subject) === false) {
            $subject = $this->get($id, false);
        }

        if ($this->authorizationService->isGranted('subjects.delete', $subject)) {
            if (!is_numeric($subject) && !($subject instanceof Subject)) {
                throw new RuntimeException(
                    'Wrong 1 argument given in SubjectsService::delete(), argument must be an integer or an instance of \Institution\Model\Subject!'
                );
            }

            return $this->mapper->delete($id);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     *
     * @param Subject|int $subject
     *
     * @return boolean
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    public function deactivate($subject)
    {
        if (!is_numeric($subject) && !($subject instanceof Subject)) {
            throw new RuntimeException(
                'Wrong 1 argument given in SubjectsService::delete(), argument must be an integer or an instance of \Institution\Model\Subject!'
            );
        }

        $id = is_numeric($subject) ? (int) $subject : $subject->id;

        if (($subject instanceof Subject) === false) {
            $subject = $this->get($id, false);
        }

        if ($this->authorizationService->isGranted('subjects.deactivate', $subject)) {
            return $this->mapper->removeBeginYear($id, $this->identity->getWorkingYear());
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     *
     * @param SubjectProgram|int $program
     * @param Subject            $subject
     *
     * @return bool
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    public function deleteProgram($program, Subject $subject)
    {
        $id = is_numeric($program) ? (int) $program : $program->id;

        if (($program instanceof SubjectProgram) === false) {
            $program = $this->getProgram($id, true, 'subjects.programs.view', $subject);
        }

        if ($this->authorizationService->isGranted('subjects.programs.delete', $subject, $program)) {
            if (!is_numeric($program) && !($program instanceof SubjectProgram)) {
                throw new RuntimeException(
                    'Wrong 1 argument given in SubjectsService::deleteProgram(), argument must be an integer or an instance of \Institution\Model\SubjectProgram!'
                );
            }

            return $this->programMapper->delete($id);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     *
     * @param bool $value
     *
     * @return \Institution\Service\SubjectsService
     */
    public function getWithTeachers(bool $value)
    {
        $this->mapper->setWithTeachers($value);

        return $this;
    }

    public function fetchAreas()
    {
        return $this->mapper->fetchAreas();
    }

    public function fetchAreasForDropDown()
    {
        $result = [];
        $areas = $this->fetchAreas();

        foreach ($areas as $subjectArea) {
            $result[$subjectArea->id] = $subjectArea->title;
        }

        return $result;
    }

    /**
     *
     * @param bool $value
     *
     * @return \Institution\Service\SubjectsService
     * @deprecated
     */
    public function setWithSubjectPrograms(bool $value)
    {
        $this->mapper->setWithSubjectPrograms($value);

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return SubjectsServiceInterface
     */
    public function getWithPrograms(bool $value = true)
    {
        $this->withPrograms = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @param int  $periodsId
     *
     * @return SubjectsService
     */
    public function getWithCurriculum(bool $value = true, int $periodsId)
    {
        $this->mapper->setWithCurriculum($value, $periodsId);

        return $this;
    }

    /**
     * @param Subject|array|\Traversable $subjects
     */
    public function appendPrograms(&$subjects)
    {
        $programs = $this->programMapper->fetch($subjects);

        if ($subjects instanceof Subject) {
            $this->appendProgramToSubject($subjects, $programs);
        } else {
            foreach ($subjects as &$subject) {
                $this->appendProgramToSubject($subject, $programs);
            }
        }

    }

    /**
     * @param SubjectSearch $search
     *
     * @return $this
     */
    public function setSearchModel(SubjectSearch $search)
    {
        $this->mapper->setSearchModel($search);

        return $this;
    }

    /**
     * @param Subject                 $subject
     * @param SubjectsProgramIterator $programs
     */
    protected function appendProgramToSubject(Subject &$subject, SubjectsProgramIterator $programs)
    {
        foreach ($programs as $program) {
            if ((int) $program->subjects_id === (int) $subject->id) {
                $subject->appendProgram($program);
            }
        }
    }

    /**
     * @param Subject $subject
     * @param string  $schoolLevel
     *
     * @return Subject
     */
    protected function filterTeachersBySchoolLevel(Subject $subject, string $schoolLevel)
    {
        $s = clone $subject;
        $s->setRelations('teacher', []);

        if (is_array($subject->teacher)) {
            foreach ($subject->teacher as $key => $teacher) {
                if (in_array($schoolLevel, $teacher->school_level)) {
                    $s->appendRelation('teacher', $teacher);
                }
            }
        }

        return $s;
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
