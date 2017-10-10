<?php

namespace Institution\Mapper;

use Institution\Iterator\SubjectIterator;
use Institution\Model\SubjectSearch;
use Zend\Db\Adapter\AdapterInterface;
use Application\Mapper\AbstractSqlMapper;
use Application\Hydrator\ClassPrefixArraySerializable;
use Zend\Db\Sql\Predicate\Expression as PredicateExpression;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Debug\Debug;
use Zend\Hydrator\ArraySerializable;
use Zend\Db\ResultSet\HydratingResultSet;
use Application\Db\ResultSet\HydratingClassPrefixResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Institution\Model\Subject;
use Institution\Model\SubjectArea;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Application\Mapper\MapperException;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class SubjectsSqlMapper extends AbstractSqlMapper implements SubjectsMapperInterface
{
    const ORDER_BY_SUBJECT_AREA = 1;
    const ORDER_BY_LEVEL = 2;

    protected $dbTable = 'subjects';
    protected $dbTableAlias = 's';

    /**
     * @var bool
     */
    protected $withTeachers = false;

    /**
     * @var bool
     */
    protected $withCurriculum = false;

    /**
     * @var int
     */
    protected $withCurriculumPeriodsId;

    /**
     *
     * @var ClassPrefixArraySerializable
     */
    protected $hydrator;

    public function __construct(
        ClassPrefixArraySerializable $hydrator,
        AdapterInterface $dbAdapter,
        $dbTable = null,
        $dbTableAlias = null
    )
    {
        $this->hydrator = $hydrator;
        parent::__construct( $dbAdapter, $dbTable, $dbTableAlias );
    }

    /**
     *
     * @param int     $accounts_id
     * @param int     $beginYear
     * @param boolean $paginated
     * @param int     $orderBy
     *
     * @return SubjectIterator|Paginator
     */
    public function fetch(
        int $accounts_id,
        int $beginYear,
        bool $paginated = false,
        int $orderBy = self::ORDER_BY_SUBJECT_AREA
    )
    {
        $where = new Where();
        $where->equalTo( $this->dbTableAlias . '.accounts_id', $accounts_id )
            ->equalTo( $this->dbTableAlias . '.deleted', 0 )
            ->equalTo( 'y.begin_year', $beginYear );

        $select = $this->getSubjectSelect( $orderBy )
            ->join(
                [ 'y' => 'subject_years' ],
                'y.subjects_id = ' . $this->dbTableAlias . '.id',
                [ ],
                Select::JOIN_INNER
            );

        $searchWhere = $this->defineSearchCriteria( $select );
        $where->addPredicate( $searchWhere );

        $select->where( $where );
//        \Zend\Debug\Debug::dump($this->getSql()->buildSqlString($select));exit;
        $resultSet = new HydratingClassPrefixResultSet( $this->hydrator, new Subject() );

        $return = null;

        if ( $paginated ) {
            $paginatorAdapter = new DbSelect( $select, $this->dbAdapter, $resultSet, $this->getSelectCount( $select ) );
            $paginator = new Paginator( $paginatorAdapter );
            $return = $paginator;
        } else {
            $st = $this->getSql()->prepareStatementForSqlObject( $select );
            $result = $st->execute();

            if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
                $resultSet->initialize( $result );
                $return = new SubjectIterator( $resultSet );
            }
        }

        return $return;
    }

    /**
     * @param array $roomIds
     * @param int   $beginYear
     *
     * @return SubjectIterator|bool
     */
    public function fetchForRooms( array $roomIds, int $beginYear )
    {
        if ( !sizeof( $roomIds ) ) {
            return false;
        }

        $select = $this->getSql()->select()
            ->join(
                [ 'y' => 'subject_years' ],
                'y.subjects_id = ' . $this->dbTableAlias . '.id',
                [ ],
                Select::JOIN_INNER
            )
            ->join(
                [ 'r' => 'rooms_subjects' ],
                'r.subjects_id = ' . $this->dbTableAlias . '.id',
                [ 'dependedRoomsId' => 'rooms_id' ],
                Select::JOIN_INNER
            );

        $where = new Where();
        $where->in( 'r.rooms_id', $roomIds )
            ->equalTo( 'y.begin_year', $beginYear )
            ->equalTo( $this->dbTableAlias . '.deleted', 0 );

        $select->where( $where );

        $st = $this->getSql()->prepareStatementForSqlObject( $select );
        $result = $st->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new HydratingResultSet( new ArraySerializable(), new Subject() );
            $resultSet->initialize( $result );

            return new SubjectIterator( $resultSet );
        } else {
            return false;
        }
    }

    /**
     *
     * @param int $id
     *
     * @return Subject|null
     */
    public function get( $id )
    {
        $select = $this->getSubjectSelect()->where(
            [ $this->dbTableAlias . '.id' => (int)$id,
              $this->dbTableAlias . '.deleted' => 0 ]
        );
        $st = $this->getSql()->prepareStatementForSqlObject( $select );

        $result = $st->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new HydratingClassPrefixResultSet( $this->hydrator, new Subject );
            $resultSet->initialize( $result );

            return $resultSet->current();
        }

        return null;
    }

    /**
     *
     * @param Subject $subject
     * @param int     $beginYear
     *
     * @return Subject|false
     * @throws MapperException
     */
    public function create( Subject $subject, int $beginYear )
    {
        if ( !$subject->getIsNewRecord() ) {
            throw new MapperException( 'The object is not a new record!' );
        }

        $subject->removeExcessColumns();
        $data = $subject->getArrayCopy();

        $insert = $this->getSql( false )->insert()->columns( $subject->getColumns() )->values( $data );
        $st = $this->getSql()->prepareStatementForSqlObject( $insert );
        $result = $st->execute();

        if ( $result->getAffectedRows() === 1 ) {
            $id = $result->getGeneratedValue();

            $insertYears = $this->getSql( false, true )->insert( 'subject_years' );
            $insertYears->columns( [ 'begin_year', 'subjects_id' ] )->values(
                [ 'begin_year' => $beginYear,
                  'subjects_id' => $id ]
            );
            $stYears = $this->getSql()->prepareStatementForSqlObject( $insertYears );
            $resultYear = $stYears->execute();

            if ( $resultYear->getAffectedRows() === 1 ) {
                $return = $this->get( $id );
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     *
     * @param Subject $subject
     *
     * @return boolean|Subject
     * @throws MapperException
     */
    public function update( Subject $subject )
    {
        if ( $subject->getIsNewRecord() ) {
            throw new MapperException( 'The record does not exists in database!' );
        }

        $subject->removeExcessColumns();
        $data = $subject->getArrayCopy();
        unset( $data[ 'id' ] );
        unset( $data[ 'accounts_id' ] );

        $update = $this->getSql( false )->update()->set( $data )->where( [ 'id' => ( (int)$subject->id ) ] );
        $st = $this->getSql()->prepareStatementForSqlObject( $update );

        if ( $st->execute() ) {
            return $subject;
        } else {
            return false;
        }
    }

    /**
     *
     * @param int $id
     *
     * @return boolean
     */
    public function delete( $id )
    {
        return $this->deleteModelInterface( $id );
    }

    /**
     *
     * @param int $id
     * @param int $beginYear
     *
     * @return boolean
     */
    public function removeBeginYear( int $id, int $beginYear )
    {
        $delete = $this->getSql( false, true )->delete( 'subject_years' );
        $delete->where( [ 'subjects_id' => $id, 'begin_year' => $beginYear ] );

        $st = $this->getSql()->prepareStatementForSqlObject( $delete );

        if ( $st->execute()->getAffectedRows() == 1 ) {
            return true;
        } else {
            return false;
        }
    }

    public function setWithTeachers( bool $value )
    {
        $this->withTeachers = $value;
    }

    public function getWithTeachers()
    {
        return (bool)$this->withTeachers;
    }

    public function setWithCurriculum( bool $value, int $periodsId )
    {
        $this->withCurriculum = $value;
        $this->withCurriculumPeriodsId = $periodsId;
    }

    public function getWithCurriculum()
    {
        return (bool)$this->withCurriculum;
    }

    public function fetchAreas()
    {
        $select = $this->getSql( false, true )->select( [ 'sa' => 'subject_areas' ] );
        $st = $this->getSql()->prepareStatementForSqlObject( $select );
        $result = $st->execute();

        $return = [ ];

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new HydratingResultSet( new ArraySerializable(), new SubjectArea() );
            $resultSet->initialize( $result );
            $return = $this->resultSetToArray( $resultSet );
        }

        return $return;
    }

    /**
     * @param int $orderBy
     *
     * @return Select
     */
    protected function getSubjectSelect( int $orderBy = self::ORDER_BY_SUBJECT_AREA )
    {
        $select = $this->getSql()->select();

        if ( $orderBy === self::ORDER_BY_SUBJECT_AREA ) {
            $select->order(
                [
                    'sa.weight DESC',
                    'sa.title ASC',
                    $this->dbTableAlias . '.title ASC',
                    $this->dbTableAlias . '.id ASC'
                ]
            );
        } elseif ( $orderBy === self::ORDER_BY_LEVEL ) {
            $select->order(
                [
                    $this->dbTableAlias . '.level DESC',
                    $this->dbTableAlias . '.title ASC',
                    $this->dbTableAlias . '.id ASC'
                ]
            );
        }

        $select->join(
            [ 'sa' => 'subject_areas' ],
            'sa.id=' . $this->dbTableAlias . '.subject_areas_id',
            [
                'Institution\Model\SubjectArea_id' => 'id',
                'Institution\Model\SubjectArea_title' => 'title',
                'Institution\Model\SubjectArea_weight' => 'weight',
            ],
            Select::JOIN_INNER
        );

//        $select->order( [ ] );

        if ( $this->getWithTeachers() === true ) {
            $select->join(
                [ 'ts' => 'teachers_subjects' ],
                'ts.subjects_id=' . $this->dbTableAlias . '.id',
                [ ],
                Select::JOIN_LEFT
            )
                ->join(
                    [ 't' => 'teachers' ],
                    new Expression( 't.id=ts.teachers_id AND t.deleted = 0' ),
                    [
                        'Institution\Model\Teacher_id' => 'id',
                        'Institution\Model\Teacher_accounts_id' => 'accounts_id',
                        'Institution\Model\Teacher_users_id' => 'users_id',
                        'Institution\Model\Teacher_firstname' => 'firstname',
                        'Institution\Model\Teacher_lastname' => 'lastname',
                        'Institution\Model\Teacher_middlename' => 'middlename',
                        'Institution\Model\Teacher_school_level' => 'school_level',
                        'Institution\Model\Teacher_email' => 'email',
                        'Institution\Model\Teacher_phone' => 'phone',
                        'Institution\Model\Teacher_vacancy' => 'vacancy',
                    ],
                    Select::JOIN_LEFT
                );

            $select->order(
                [ 't.vacancy ASC', 't.lastname ASC', 't.firstname ASC',
                  't.middlename ASC' ]
            );
        }

        if ( $this->getWithCurriculum() === true ) {
            $expression = new Expression(
                'c.subjects_id = ' . $this->dbTableAlias . '.id AND c.periods_id = ?', [
                                                                                         $this->withCurriculumPeriodsId
                                                                                     ]
            );

            $select->join(
                [ 'c' => 'curriculum' ],
                $expression,
                [
                    'Curriculum\Model\Curriculum_periods_id' => 'periods_id',
                    'Curriculum\Model\Curriculum_teachers_id' => 'teachers_id',
                    'Curriculum\Model\Curriculum_subjects_id' => 'subjects_id',
                    'Curriculum\Model\Curriculum_groups_id' => 'groups_id',
                    'Curriculum\Model\Curriculum_subgroups_id' => 'subgroups_id',
                    'Curriculum\Model\Curriculum_hours' => 'hours',

                ],
                Select::JOIN_LEFT
            );
        }

        return $select;
    }

    protected function getSelectCount( Select $select )
    {
        $selectNew = clone $select;
        $selectNew->reset( Select::LIMIT );
        $selectNew->reset( Select::OFFSET );
        $selectNew->reset( Select::ORDER );
//        $selectNew->reset( Select::JOINS );
        $selectNew->group( $this->dbTableAlias . '.id' );

//        $selectNew->join(
//            [ 'y' => 'subject_years' ],
//            'y.subjects_id = ' . $this->dbTableAlias . '.id',
//            [ ],
//            Select::JOIN_INNER
//        );

        $countSelect = new Select;

        $countSelect->columns( [ DbSelect::ROW_COUNT_COLUMN_NAME => new Expression( 'COUNT(1)' ) ] );
        $countSelect->from( [ 'original_select' => $selectNew ] );

        return $countSelect;
    }

    protected function defineSearchCriteria( Select $select )
    {
        $where = new Where();
        $where->addPredicate( new PredicateExpression( '1' ) );

        if ( $this->searchModel instanceof SubjectSearch ) {
            if ( $this->searchModel->title ) {
                $where->like( $this->dbTableAlias . '.title', '%' . $this->searchModel->title . '%' );
            }

            if ( $this->searchModel->titleShort ) {
                $where->like( $this->dbTableAlias . '.title_short', '%' . $this->searchModel->titleShort . '%' );
            }

            if ( $this->searchModel->level ) {
                $where->equalTo( $this->dbTableAlias . '.level', $this->searchModel->level );
            }

            if ( $this->searchModel->programs ) {
                $select->join(
                    [ 'p' => 'subjects_programs' ],
                    'p.subjects_id = ' . $this->dbTableAlias . '.id',
                    [ ],
                    Select::JOIN_INNER
                );
                $where->like( 'p.title', '%' . $this->searchModel->programs . '%' );
            }
        }

        return $where;
    }

}
