<?php

namespace Institution\Mapper;

use Institution\Iterator\GroupIterator;
use Institution\Model\Group;
use Institution\Model\Subgroup;

interface GroupsMapperInterface
{

	/**
	 *
	 * @param int $accounts_id
	 * @param int $state_year
	 * @return GroupIterator
	 */
	public function fetch( $accounts_id, $state_year );

	/**
	 *
	 * @param int $id
	 * @param int $state_year
	 *
	 * @return Group
	 */
	public function get( $id, $state_year );

	/**
	 *
	 * @param Group $group
	 * @return Group|false
	 */
	public function create( Group $group );

	/**
	 *
	 * @param Group $group
	 * @return Group
	 */
	public function update( Group $group );

	/**
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function delete( $id );

	/**
	 *
	 * @param bool $value
	 */
	public function setWithActiveGradeOnly( bool $value );

	/**
	 * @return bool
	 */
	public function getWithActiveGradeOnly();

	/**
	 *
	 * @param bool $value
	 * @param int $periodId
	 * @return $this
	 */
	public function setWithSubgroups( bool $value, int $periodId, int $subjectId );

	/**
	 * @return bool
	 */
	public function getWithSubgroups();

	/**
	 * @return int
	 */
	public function getWithSubgroupsPeriodId();

	/**
	 * @return int
	 */
	public function getWithSubgroupsSubjectId();

	/**
	 *
	 * @param int $groupId
	 * @param int $periodId
	 * @param int $subjectId
	 * @return Subgroup[]
	 */
	public function getSubgroups( int $groupId, int $periodId, int $subjectId );

	/**
	 *
	 * @param Subgroup $subgroup
	 * @return Subgroup|false
	 */
	public function createSubgroup( Subgroup $subgroup );

	/**
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function deleteSubgroup( int $id );
}
