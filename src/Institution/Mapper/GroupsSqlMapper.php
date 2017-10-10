<?php

namespace Institution\Mapper;

use Institution\Iterator\GroupIterator;
use Zend\Db\Adapter\AdapterInterface;
use Application\Hydrator\ClassPrefixArraySerializable;
use Application\Mapper\AbstractSqlMapper;
use Institution\Model\Group;
use Institution\Model\Subgroup;
use Zend\Debug\Debug;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Application\Mapper\MapperException;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\ArraySerializable;

class GroupsSqlMapper extends AbstractSqlMapper implements GroupsMapperInterface
{

    protected $dbTable = 'groups';
    protected $dbTableAlias = 'g';

    /**
     *
     * @var bool
     */
    protected $withActiveGradeOnly = false;

    /**
     *
     * @var bool
     */
    protected $withSubgroups = false;

    /**
     *
     * @var int
     */
    protected $withSubgroupsPeriodId;

    /**
     *
     * @var int
     */
    protected $withSubgroupSubjectId;

    public function __construct( AdapterInterface $dbAdapter, Group $objectPrototype, $dbTable = null, $dbTableAlias = null )
    {
        parent::__construct( $dbAdapter, $dbTable, $dbTableAlias );
        $this->setObjectPrototype( $objectPrototype );
        $this->setHydrator( new ClassPrefixArraySerializable() );
    }

    /**
     *
     * @param int $accounts_id
     * @param int $state_year
     *
     * @throws MapperException
     * @return GroupIterator
     */
    public function fetch( $accounts_id, $state_year = null )
    {
        if ( $state_year && !is_numeric( $state_year ) ) {
            $state_year = null;
        }

        $objectPrototype = $this->getObjectPrototype();
        $objectPrototype->setStateYear( $state_year );
        $this->setObjectPrototype($objectPrototype);

        $this->setModelIteratorPrototype(new GroupIterator(null,$objectPrototype));

        $where = new Where();
        $where->equalTo( $this->dbTableAlias . '.accounts_id', $accounts_id )
            ->equalTo( $this->dbTableAlias . '.deleted', 0 );

        if ( $this->getWithActiveGradeOnly() === true ) {
            $where->equalTo( 'grades.begin_year', $objectPrototype->getStateYear()/*$state_year*/ );
        }

        return $this->fetchAbstractModelInterafce( $where, false );
    }

    /**
     *
     * @param int $id
     * @param int $state_year
     *
     * @return Group|null
     */
    public function get( $id, $state_year = null )
    {
        if ( $state_year && !is_numeric( $state_year ) ) {
            $state_year = null;
        }

        $objectPrototype = $this->getObjectPrototype();
        $objectPrototype->setStateYear( $state_year );

//        $this->setObjectPrototype( $objectPrototype );

        return $this->getModelInterface( $id );
    }

    /**
     *
     * @param Group $group
     *
     * @return Group|false
     * @throws \Application\Mapper\MapperException
     */
    public function create( Group $group )
    {
        return $this->createModelInterface( $group );
    }

    /**
     *
     * @param Group $group
     *
     * @return Group|false
     * @throws \Application\Mapper\MapperException
     */
    public function update( Group $group )
    {
        return $this->updateModelInterface( $group );
    }

    /**
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete( $id )
    {
        return $this->deleteModelInterface( $id );
    }

    public function setWithActiveGradeOnly( bool $value )
    {
        $this->withActiveGradeOnly = $value;
    }

    public function getWithActiveGradeOnly()
    {
        return ( bool ) $this->withActiveGradeOnly;
    }

    /**
     *
     * @param bool     $value
     * @param null|int $periodId
     * @param null|int $subjectId
     *
     * @return \Institution\Mapper\GroupsSqlMapper
     */
    public function setWithSubgroups( bool $value, int $periodId = null, int $subjectId = null )
    {
        if ( $value === true && !$periodId ) {
            $value = false;
        }

        $this->withSubgroups = $value;
        $this->withSubgroupsPeriodId = $periodId;
        $this->withSubgroupSubjectId = $subjectId;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getWithSubgroups()
    {
        return $this->withSubgroups;
    }

    /**
     *
     * @return int
     */
    public function getWithSubgroupsPeriodId()
    {
        return $this->withSubgroupsPeriodId;
    }

    /**
     *
     * @return int
     */
    public function getWithSubgroupsSubjectId()
    {
        return $this->withSubgroupSubjectId;
    }

    /**
     *
     * @param int $groupId
     * @param int $periodId
     * @param int $subjectId
     *
     * @return Subgroup[]
     */
    public function getSubgroups( int $groupId, int $periodId, int $subjectId )
    {
        $select = $this->getSql( false, true )->select( 'subgroups' )
            ->where( [
                'groups_id'   => $groupId,
                'periods_id'  => $periodId,
                'subjects_id' => $subjectId
            ] )
            ->order( 'title ASC' );

        $st = $this->getSql()->prepareStatementForSqlObject( $select );
        $result = $st->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new HydratingResultSet( new ArraySerializable(), new Subgroup() );
            $resultSet->initialize( $result );

            return $this->resultSetToArray( $resultSet );
        }

        return null;
    }

    /**
     *
     * @param Subgroup $subgroup
     *
     * @return boolean|Subgroup
     * @throws MapperException
     */
    public function createSubgroup( Subgroup $subgroup )
    {
        if ( !$subgroup->getIsNewRecord() ) {
            throw new MapperException( 'The object is not a new record!' );
        }

        $subgroup->removeExcessColumns();
        $data = $subgroup->getArrayCopy();

        $insert = $this->getSql( false, true )->insert( 'subgroups' )->columns( $subgroup->getColumns() )->values( $data );
        $st = $this->getSql()->prepareStatementForSqlObject( $insert );
        $result = $st->execute();

        if ( $result->getAffectedRows() === 1 ) {
            $subgroup->id = $result->getGeneratedValue();
            $subgroup->setIsNewRecord( false );

            return $subgroup;
        } else {
            return false;
        }
    }

    public function deleteSubgroup( int $id )
    {
        $delete = $this->getSql( false, true )->update( 'subgroups' )->set( [ 'deleted' => 1 ] )->where( [ 'id' => $id ] );

        $st = $this->getSql()->prepareStatementForSqlObject( $delete );

        if ( $st->execute()->getAffectedRows() == 1 ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getSelect()
    {
        $select = $this->getSql()->select()
            ->join( 'grades', 'grades.groups_id=' . $this->dbTableAlias . '.id', [
                'Institution\Model\Grade_groups_id'      => 'groups_id',
                'Institution\Model\Grade_begin_year'     => 'begin_year',
                'Institution\Model\Grade_level'          => 'level',
                'Institution\Model\Grade_students_count' => 'students_count',
                'Institution\Model\Grade_male_count'     => 'male_count',
                'Institution\Model\Grade_female_count'   => 'female_count',
                'Institution\Model\Grade_final'          => 'final',
            ], Select::JOIN_LEFT )
            ->order( [ 'grades.level ASC', $this->dbTableAlias . '.letter ASC' ] );

        if ( $this->getWithSubgroups() ) {
            if ( $this->getWithSubgroupsSubjectId() ) {
                $expression = new Expression( 'sg.groups_id = ' . $this->dbTableAlias . '.id AND sg.periods_id = ? AND sg.subjects_id = ? AND sg.deleted=0', [
                    $this->getWithSubgroupsPeriodId(),
                    $this->getWithSubgroupsSubjectId()
                ] );
            } else {
                $expression = new Expression( 'sg.groups_id = ' . $this->dbTableAlias . '.id AND sg.periods_id = ? AND sg.deleted=0', [
                    $this->getWithSubgroupsPeriodId()
                ] );
            }

            $select->join( [ 'sg' => 'subgroups' ], $expression, [
                'Institution\Model\Subgroup_id'          => 'id',
                'Institution\Model\Subgroup_groups_id'   => 'groups_id',
                'Institution\Model\Subgroup_subjects_id' => 'subjects_id',
                'Institution\Model\Subgroup_periods_id'  => 'periods_id',
                'Institution\Model\Subgroup_title'       => 'title',
            ], Select::JOIN_LEFT )->order( [ 'sg.title ASC' ] );
        }

        return $select;
    }

    protected function getSelectCount( $select )
    {
        $selectNew = clone $select;
        $selectNew->reset( Select::LIMIT );
        $selectNew->reset( Select::OFFSET );
        $selectNew->reset( Select::ORDER );
        $selectNew->reset( Select::JOINS );

        $countSelect = new Select;

        $countSelect->columns( [ DbSelect::ROW_COUNT_COLUMN_NAME => new Expression( 'COUNT(1)' ) ] );
        $countSelect->from( [ 'original_select' => $selectNew ] );

        return $countSelect;
    }

}
