<?php

namespace Institution\Mapper;

use Institution\Model\TeacherSearch;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Application\Hydrator\ClassPrefixArraySerializable;
use Zend\Hydrator\ArraySerializable;
use Zend\Db\Adapter\Driver\ResultInterface;
use Application\Mapper\AbstractSqlMapper;
use Institution\Model\Teacher;
use Institution\Model\Subject;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as PredicateExpression;

class TeachersSqlMapper extends AbstractSqlMapper implements TeachersMapperInterface
{

    static protected $withSubjects = true;

    /**
     *
     * @var string DB table name of teachers
     */
    protected $dbTable = 'teachers';

    /**
     *
     * @var string DB table alias
     */
    protected $dbTableAlias = 't';

    /**
     * @var Select
     */
    protected $select;

    static public function setWithSubjects(bool $withSubject)
    {
        self::$withSubjects = $withSubject;
    }

    static protected function pullWithSubjects()
    {
        return self::$withSubjects;
    }

    public function __construct(
        AdapterInterface $dbAdapter,
        $dbTable = null,
        $dbTableAlias = null,
        ClassPrefixArraySerializable $hydrator = null
    )
    {
        parent::__construct($dbAdapter, $dbTable, $dbTableAlias, $hydrator);
        $this->setObjectPrototype(new Teacher());
    }

    /**
     *
     * @param int     $accounts_id
     * @param boolean $paginated
     *
     * @return Teacher[]|\Zend\Paginator\Paginator
     */
    public function fetch($accounts_id, $paginated)
    {
        $where = new Where();
        $where->equalTo($this->dbTableAlias . '.accounts_id', $accounts_id)
            ->equalTo($this->dbTableAlias . '.deleted', 0);

        $searchWhere = $this->defineSearchCriteria();
        $where->addPredicate($searchWhere);

        $teachers = $this->fetchAbstractModelInterafce($where, $paginated);

        if (self::pullWithSubjects() && !$paginated) {
            $this->appendSubjects($teachers);
        }

        return $teachers;
    }

    /**
     *
     * @param int $id
     *
     * @return Teacher|null
     */
    public function get($id)
    {
        $teacher = $this->getModelInterface($id);

        if (self::pullWithSubjects()) {
            $this->appendSubjects($teacher);
        }

        return $teacher;
    }

    /**
     *
     * @param Teacher $teacher
     *
     * @return Teacher|null
     * @throws \Application\Mapper\MapperException
     */
    public function create(Teacher $teacher)
    {
        $subjects_id = $teacher->subjects_id;

        if ($teacher = $this->createModelInterface($teacher)) {
            $this->updateTeachersSubjects($teacher, $subjects_id);
        }

        return $teacher;
    }

    /**
     *
     * @param Teacher $teacher
     *
     * @return Teacher
     * @throws \Application\Mapper\MapperException
     */
    public function update(Teacher $teacher)
    {
        $subjects_id = $teacher->subjects_id;

        $teacher->vacancy = false;

        if ($teacher = $this->updateModelInterface($teacher)) {
            $this->updateTeachersSubjects($teacher, $subjects_id);
        }

        return $teacher;
    }

    public function updateTeachersSubjects(Teacher $teacher, array $subjects_id = null)
    {
        $delete = $this->getSql(false, true)->delete('teachers_subjects')
            ->where(['teachers_id' => $teacher->id]);

        $stDelete = $this->getSql()->prepareStatementForSqlObject($delete);
        $stDelete->execute();

        if (sizeof($subjects_id)) {
            $sqlString = 'INSERT INTO `teachers_subjects` (`teachers_id`, `subjects_id`)
                VALUES (' . ((int) $teacher->id) . ',' . implode('),(' . ((int) $teacher->id) . ',', $subjects_id)
                . ')';
            $this->dbAdapter->query($sqlString, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->deleteModelInterface($id);
    }

    /**
     *
     * @param Subject $subject
     *
     * @return Teacher
     */
    public function addVacancy(Subject $subject)
    {
        $vacancy = $this->getSubjectVacancy($subject);

        if ($vacancy instanceof Teacher) {
            $firstname = is_numeric($vacancy->firstname) ? (int) $vacancy->firstname : 0;
            $firstname++;
        } else {
            $firstname = 1;
        }

        $properties = [
            'accounts_id' => $subject->accounts_id,
            'users_id' => null,
            'firstname' => $firstname,
            'lastname' => $subject->title_short,
            'middlename' => null,
            'email' => null,
            'phone' => null,
            'vacancy' => 1
        ];

        $teacher = new Teacher($properties);
        $teacher->subjects_id = [$subject->id];

        return $this->create($teacher);
    }

    /**
     *
     * @param Select|array|\Iterator|\IteratorAggregate|Teacher $teachers
     *
     * @return TeachersMapperInterface
     */
    public function appendSubjects(&$teachers)
    {
        $subjects = $this->fetchSubjects($teachers);

        if ($teachers instanceof Teacher) {
            $this->appendSubjectsToTeacher($teachers, $subjects);
        } else {
            foreach ($teachers as &$teacher) {
                $this->appendSubjectsToTeacher($teacher, $subjects);
            }
        }

        return $this;
    }

    /**
     *
     * @param Teacher $teacher
     * @param array   $subjects
     *
     * @return \Institution\Mapper\TeachersSqlMapper
     */
    protected function appendSubjectsToTeacher(&$teacher, $subjects)
    {
        foreach ($subjects as $subject) {
            if ($subject->getDependedTeachersId() === (int) $teacher->id) {
                $teacher->appendRelation('subject', $subject);
            }
        }

        return $this;
    }

    /**
     *
     * @param Subject $subject
     *
     * @return Teacher|null
     */
    protected function getSubjectVacancy(Subject $subject)
    {
        $vacancy = null;

        $select = $this->getSql()->select()
            ->where(['accounts_id' => $subject->accounts_id, 'lastname' => $subject->title_short])
            ->order('firstname DESC')
            ->limit(1);

        $stVacancy = $this->getSql()->prepareStatementForSqlObject($select);
        $result = $stVacancy->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new HydratingResultSet(new ArraySerializable(), $this->getObjectPrototype());
            $vacancy = $resultSet->initialize($result)->current();
        }

        return $vacancy;
    }

    /**
     *
     * @param Select|array|\Iterator|\IteratorAggregate|Teacher $teachers
     *
     * @return array
     */
    protected function fetchSubjects($teachers)
    {
        $inCondition = null;

        if ($teachers instanceof Select) {
            $inCondition = clone $teachers;
            $inCondition->columns(['id']);
        } elseif (
            $teachers instanceof \IteratorAggregate ||
            $teachers instanceof \Iterator ||
            is_array($teachers)
        ) {
            $inCondition = [];
            foreach ($teachers as $t) {
                $inCondition[] = $t->id;
            }
        } elseif ($teachers instanceof Teacher) {
            $inCondition = [$teachers->id];
        }

        if (!sizeof($inCondition)) {
            return [];
        }

        $select = $this->getSql(false, true)->select(['s' => 'subjects'])
            ->join(
                ['ts' => 'teachers_subjects'],
                'ts.subjects_id = s.id',
                ['depended_teachers_id' => 'teachers_id'],
                Select::JOIN_INNER
            );
        $where = $select->where;
        $where->in('ts.teachers_id', $inCondition);
        $select->where($where);

        $resultSet = new HydratingResultSet(new ArraySerializable(), new Subject());

        $st = $this->getSql()->prepareStatementForSqlObject($select);
        $result = $st->execute();

        $subjects = [];

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet->initialize($result);
            $subjects = $this->resultSetToArray($resultSet);
        }

        return $subjects;
    }

    protected function getSelect()
    {
        if (!$this->select instanceof Select) {
            $this->select = $this->getSql()->select()->order(
                [
                    'dismissed ASC',
                    'vacancy ASC',
                    'lastname ASC',
                    'firstname ASC',
                    'middlename ASC',
                    'id ASC'
                ]
            );
        }

        return $this->select;
    }

    protected function getSelectCount($select)
    {
        $selectNew = clone $select;
        $selectNew->reset(Select::LIMIT);
        $selectNew->reset(Select::OFFSET);
        $selectNew->reset(Select::ORDER);
        //$selectNew->reset( Select::JOINS );
        $selectNew->group($this->dbTableAlias . '.id');

        $countSelect = new Select;

        $countSelect->columns([DbSelect::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $selectNew]);

        return $countSelect;
    }

    protected function defineSearchCriteria()
    {
        $where = new Where();
        $where->addPredicate(new PredicateExpression('1'));

        if ($this->searchModel instanceof TeacherSearch) {
            if ($this->searchModel->fullname) {
                $name = explode(' ', $this->searchModel->fullname);

                if (sizeof($name) === 3) {
                    $where->NEST
                        ->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[2] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[2] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[1] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[2] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[2] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[0] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[2] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[0] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[2] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[1] . '%')
                        ->UNNEST->UNNEST;
                } elseif (sizeof($name) === 2) {
                    $where->NEST
                        ->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[1] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.firstname', '%' . $name[0] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.firstname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[0] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.firstname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[1] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[0] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[1] . '%')
                        ->UNNEST
                        ->OR->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[1] . '%')
                        ->and->like($this->dbTableAlias . '.middlename', '%' . $name[0] . '%')
                        ->UNNEST->UNNEST;
                } elseif (sizeof($name) === 1) {
                    $where->NEST->like($this->dbTableAlias . '.lastname', '%' . $name[0] . '%')
                        ->OR->like($this->dbTableAlias . '.firstname', '%' . $name[0] . '%')
                        ->OR->like($this->dbTableAlias . '.middlename', '%' . $name[0] . '%')
                        ->UNNEST;
                }
            }

            if ($this->searchModel->subjects) {
                $this->getSelect()->join(
                    ['rs' => 'teachers_subjects'],
                    'rs.teachers_id = ' . $this->dbTableAlias . '.id',
                    [],
                    Select::JOIN_INNER
                )->join(
                    ['s' => 'subjects'],
                    new Expression('s.id=rs.subjects_id AND s.deleted = 0'),
                    [],
                    Select::JOIN_INNER
                );
                $where->like('s.title', '%' . $this->searchModel->subjects . '%');
            }

            if ($this->searchModel->email) {
                $where->like($this->dbTableAlias . '.email', '%' . $this->searchModel->email . '%');
            }

            if ($this->searchModel->phone) {
                $where->like($this->dbTableAlias . '.email', '%' . $this->searchModel->phone . '%');
            }

            if( is_numeric($this->searchModel->vacancy) ){
                $where->equalTo($this->dbTableAlias.'.vacancy', $this->searchModel->vacancy);
            }

            if( is_numeric($this->searchModel->dismissed) ){
                $where->equalTo($this->dbTableAlias.'.dismissed', $this->searchModel->dismissed);
            }
        }

        return $where;
    }
}
