<?php

namespace Institution\Mapper;

use Institution\Iterator\SubjectsProgramIterator;
use Institution\Model\Subject;
use Zend\Db\Adapter\AdapterInterface;
use Application\Mapper\AbstractSqlMapper;
use Application\Hydrator\ClassPrefixArraySerializable;
use Application\Db\ResultSet\HydratingClassPrefixResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Institution\Model\SubjectProgram;
use Application\Mapper\MapperException;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Where;
use Zend\Stdlib\Hydrator\ArraySerializable;

class SubjectsProgramsSqlMapper extends AbstractSqlMapper
    implements SubjectsProgramsMapperInterface
{

    protected $dbTable = 'subjects_programs';
    protected $dbTableAlias = 'sp';

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
     * @param int|array|\Traversable|Subject $subjects
     *
     * @return SubjectsProgramIterator
     */
    public function fetch( $subjects )
    {
        $inCondition = [ ];

        if ( is_numeric( $subjects ) ) {
            $inCondition = [ (int) $subjects ];
        } elseif ( is_array( $subjects ) ) {
            $inCondition = $subjects;
        } elseif ( $subjects instanceof \Traversable ) {
            foreach ( $subjects as $subject ) {
                $inCondition[] = $subject->id;
            }
        } elseif ( $subjects instanceof Subject ) {
            $inCondition = [ $subjects->id ];
        }

        if ( !sizeof( $inCondition ) ) {
            return [ ];
        }

        $where = new Where();
        $where->in( $this->dbTableAlias . '.subjects_id', $inCondition )
            ->equalTo( $this->dbTableAlias . '.deleted', 0 );

        $select = $this->getSql()->select()->where( $where );

        $resultSet = new HydratingResultSet( new ArraySerializable(), new SubjectProgram() );

        $st = $this->getSql()->prepareStatementForSqlObject( $select );
        $result = $st->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet->initialize( $result );

            return new SubjectsProgramIterator( $resultSet );//$this->resultSetToArray( $resultSet );
        }

        return null;
    }

    /**
     *
     * @param int $id
     *
     * @return SubjectProgram|null
     */
    public function get( $id )
    {
        $select = $this->getSql()->select()->where(
            [ $this->dbTableAlias . '.id' => ( int ) $id, $this->dbTableAlias . '.deleted' => 0 ]
        );
        $st = $this->getSql()->prepareStatementForSqlObject( $select );

        $result = $st->execute();

        if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
            $resultSet = new HydratingClassPrefixResultSet( $this->hydrator, new SubjectProgram() );
            $resultSet->initialize( $result );

            return $resultSet->current();
        }

        return null;
    }

    /**
     *
     * @param SubjectProgram $subjectProgram
     *
     * @return SubjectProgram|false
     * @throws MapperException
     */
    public function create( SubjectProgram $subjectProgram )
    {
        if ( !$subjectProgram->getIsNewRecord() ) {
            throw new MapperException( 'The object is not a new record!' );
        }

        $subjectProgram->removeExcessColumns();
        $data = $subjectProgram->getArrayCopy();

        $insert = $this->getSql( false )->insert()->columns( $subjectProgram->getColumns() )->values( $data );

        $st = $this->getSql()->prepareStatementForSqlObject( $insert );

        $result = $st->execute();

        if ( $result->getAffectedRows() == 1 ) {
            $id = $result->getGeneratedValue();

            return $this->get( $id );
        } else {
            return false;
        }
    }

    /**
     *
     * @param SubjectProgram $subjectProgram
     *
     * @return boolean|SubjectProgram
     * @throws MapperException
     */
    public function update( SubjectProgram $subjectProgram )
    {
        if ( $subjectProgram->getIsNewRecord() ) {
            throw new MapperException( 'The record does not exists in database!' );
        }

        $subjectProgram->removeExcessColumns();
        $data = $subjectProgram->getArrayCopy();
        unset( $data['id'] );
        unset( $data['subjects_id'] );

        $update = $this->getSql( false )->update()->set( $data )->where( [ 'id' => ( ( int ) $subjectProgram->id ) ] );
        $st = $this->getSql()->prepareStatementForSqlObject( $update );

        if ( $st->execute() ) {
            return $subjectProgram;
        } else {
            return false;
        }
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

}
