<?php

namespace Institution\Service;

use Institution\Model\Grade;
use Institution\Model\Group;

interface GradesServiceInterface
{

	/**
	 *
     * @param Group|int $group
	 * @return Grade[]
	 */
	public function fetch( $group );

	/**
	 *
	 * @param int $groups_id
     * @param int $begin_year
	 * @param boolean $secured Whether to check permission with assertion
	 * @param string $permission Permission code
	 * @return Grade|null
	 */
	public function get( $groups_id, $begin_year, $secured, $permission );

	/**
	 *
	 * @param Grade $grade
	 * @param Group $group
	 * @return Grade
	 */
	public function save( Grade $grade, Group $group );

	/**
	 *
	 * @param Grade $grade
	 * @return boolean
	 */
	public function delete( Grade $grade, Group $group );
}
