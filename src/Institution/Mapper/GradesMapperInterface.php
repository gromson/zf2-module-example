<?php

namespace Institution\Mapper;

use Institution\Model\Grade;

interface GradesMapperInterface
{

	/**
	 *
	 * @param int $groups_id
	 * @return Grade[]
	 */
	public function fetch( $groups_id );

	/**
	 *
	 * @param int $groups_id
	 * @param int $begin_year
	 * @return Grade
	 */
	public function get( int $groups_id, int $begin_year );

	/**
	 *
	 * @param Grade $grade
	 * @return Grade|false
	 */
	public function create( Grade $grade );

	/**
	 *
	 * @param Grade $grade
	 * @return Grade
	 */
	public function update( Grade $grade );

	/**
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete( $id );
}
