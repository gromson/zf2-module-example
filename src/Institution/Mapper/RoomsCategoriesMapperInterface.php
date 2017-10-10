<?php

namespace Institution\Mapper;

use Institution\Model\RoomCategory;

interface RoomsCategoriesMapperInterface
{

	/**
	 * @param int $accounts_id
	 * @return \Iterator|RoomCategory[] Description
	 */
	public function fetch( $accounts_id );
}
