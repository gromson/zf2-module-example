<?php

namespace Institution\Mapper;

use Application\Mapper\AbstractSqlMapper;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Institution\Model\RoomCategory;

class RoomsCategoriesSqlMapper extends AbstractSqlMapper
	implements RoomsCategoriesMapperInterface
{

	protected $dbTable = 'rooms_categories';
	protected $dbTableAlias = 'rc';

	public function fetch( $accounts_id )
	{
		$select = $this->getSql()->select();

		$where = $select->where
			->equalTo( $this->dbTableAlias . '.accounts_id', $accounts_id )
			->equalTo( $this->dbTableAlias . '.deleted', 0 );


		$select->where( $where )->order( $this->dbTableAlias . '.sort DESC' );

		$st = $this->getSql()->prepareStatementForSqlObject( $select );
		$result = $st->execute();

		$resultArray = [ ];

		if ( $result instanceof ResultInterface && $result->isQueryResult() ) {
			$resultSet = new HydratingResultSet( new ArraySerializable, new RoomCategory );
			$resultSet->initialize( $result );
			$resultArray = $this->resultSetToArray( $resultSet );
		}

		return $resultArray;
	}

}
