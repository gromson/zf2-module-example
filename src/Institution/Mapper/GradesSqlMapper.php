<?php

namespace Institution\Mapper;

use Zend\Db\Adapter\AdapterInterface;
use Application\Db\ResultSet\HydratingClassPrefixResultSet;
use Application\Hydrator\ClassPrefixArraySerializable;
use Application\Mapper\AbstractSqlMapper;
use Institution\Model\Grade;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class GradesSqlMapper extends AbstractSqlMapper implements GradesMapperInterface
{

	protected $dbTable = 'grades';
	protected $dbTableAlias = 'g';

	public function __construct( AdapterInterface $dbAdapter, $dbTable = null, $dbTableAlias = null )
	{
		parent::__construct( $dbAdapter, $dbTable, $dbTableAlias );
		$this->setObjectPrototype( new Grade() );
		$this->setHydrator( new ClassPrefixArraySerializable() );
	}

	public function fetch( $groups_id )
	{
		$where = new Where();
		$where->equalTo( $this->dbTableAlias . '.groups_id', $groups_id );

		$this->getSql()->select();

		return $this->fetchAbstractModelInterafce( $where, false );
	}

	/**
	 *
	 * @param int $groups_id
	 * @param int $begin_year
	 * @return Grade|null
	 */
	public function get( int $groups_id, int $begin_year )
	{
		$select = $this->getSql()->select();
		$select->where( ['groups_id' => $groups_id, 'begin_year' => $begin_year ] );

		$st = $this->getSql()->prepareStatementForSqlObject( $select );

		$result = $st->execute();

		if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
			$resultSet = new HydratingClassPrefixResultSet( $this->getHydrator(), $this->getObjectPrototype() );
			$resultSet->initialize( $result );

			return $resultSet->current();
		}

		return null;
	}

	/**
	 *
	 * @param Grade $grade
	 * @return Grade|null
	 * @throws \Application\Mapper\MapperException
	 */
	public function create( Grade $grade )
	{
		if ( !$grade->isNewRecord() ) {
			throw new MapperException( 'The object is not a new record!' );
		}

		$grade->removeExcessColumns();
		$data = $grade->getArrayCopy();
		unset( $data[ 'isLastYear' ] );

		$insert = $this->getSql( false )->insert()->columns( $grade->getColumns() )->values( $data );
		$st = $this->getSql()->prepareStatementForSqlObject( $insert );

		$result = $st->execute();

		if ( $result->getAffectedRows() == 1 ) {
			return $this->get( $grade->groups_id, $grade->begin_year );
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param Grade $grade
	 * @return Grade
	 * @throws \Application\Mapper\MapperException
	 */
	public function update( Grade $grade )
	{
		if ( $grade->isNewRecord() ) {
			throw new MapperException( 'The record does not exists in database!' );
		}

		$grade->removeExcessColumns();
		$data = $grade->getArrayCopy();

		unset( $data[ 'groups_id' ] );
		unset( $data[ 'begin_year' ] );
		unset( $data[ 'isLastYear' ] );

		$update = $this->getSql( false )->update()->set( $data )->where( $grade->getPk() );
		$st = $this->getSql()->prepareStatementForSqlObject( $update );

		if ( $st->execute() ) {
			return $grade;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param Grade $grade
	 * @return bool
	 */
	public function delete( $grade )
	{
		$delete = $this->getSql( false )->delete()->where( $grade->getPk() );

		$st = $this->getSql()->prepareStatementForSqlObject( $delete );

		if ( $st->execute()->getAffectedRows() == 1 ) {
			return true;
		} else {
			return false;
		}
	}

//	protected function getSelect()
//	{
//		return $this->getSql()->select()
//				->join( ['ggc' => 'grades_grades_categories' ], 'ggc.grades_id=' . $this->dbTableAlias . '.id', [ ], Select::JOIN_LEFT )
//				->join( ['gc' => 'grades_categories' ], new Expression( 'gc.id = ggc.grades_categories_id AND gc.deleted = 0' ), [
//					'Institution\Model\GradeCategory_id' => 'id',
//					'Institution\Model\GradeCategory_accounts_id' => 'accounts_id',
//					'Institution\Model\GradeCategory_title' => 'title',
//					], Select::JOIN_LEFT )
//				->order( [$this->dbTableAlias . '.grade ASC', $this->dbTableAlias . '.letter ASC' ] );
//	}

	protected function getSelectCount( $select )
	{
		$selectNew = clone $select;
		$selectNew->reset( Select::LIMIT );
		$selectNew->reset( Select::OFFSET );
		$selectNew->reset( Select::ORDER );
		$selectNew->reset( Select::JOINS );

		$countSelect = new Select;

		$countSelect->columns( array( DbSelect::ROW_COUNT_COLUMN_NAME => new Expression( 'COUNT(1)' ) ) );
		$countSelect->from( array( 'original_select' => $selectNew ) );

		return $countSelect;
	}

}
