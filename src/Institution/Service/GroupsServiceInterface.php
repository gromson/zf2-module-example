<?php

namespace Institution\Service;

use Institution\Iterator\GroupIterator;
use Institution\Model\Group;
use Institution\Model\Subgroup;

interface GroupsServiceInterface
{

    const ELEMENTARY_SCHOOL = 'elementary';
    const MIDDLE_SCHOOL     = 'middle';
    const HIGH_SCHOOL       = 'high';
	const ELEMENTARY_SCHOOL_LOW_EDGE	 = 1;
	const ELEMENTARY_SCHOOL_HIGH_EDGE	 = 4;
	const MIDDLE_SCHOOL_LOW_EDGE		 = 5;
	const MIDDLE_SCHOOL_HIGH_EDGE		 = 9;
	const HIGH_SCHOOL_LOW_EDGE		 = 10;
	const HIGH_SCHOOL_HIGH_EDGE		 = 11;

	/**
	 *
	 * @param int $level Grade level
	 * @return int
	 */
	static public function getSchoolGradeCategory( int $level );

	/**
	 *
	 * @param string $category self::ELEMENTARY_SCHOOL, self::MIDDLE_SCHOOL or self::HIGH_SCHOOL
	 * @return array
	 */
	static public function getSchoolCategoryEdges( string $category );

	/**
	 *
	 * @param int $state_year
	 * @param null|string $category one of the self::*_SCHOOL constants
	 * @return GroupIterator
	 */
	public function fetch( $state_year = null, string $category = null );

	/**
	 *
	 * @param int $id
	 * @param int $state_year
	 * @param boolean $secured Whether to check permission with assertion
	 * @param string $permission Permission code
	 * @return Group|null
	 */
	public function get( $id, $state_year, $secured, $permission );

	/**
	 *
	 * @param Group $group
	 * @return Group
	 */
	public function save( Group $group );

	/**
	 *
	 * @param Group|int $group
	 * @return boolean
	 */
	public function delete( $group );

	/**
	 *
	 * @param bool $value
	 * @return $this
	 */
	public function setWithActiveGradeOnly( bool $value );

	/**
	 *
	 * @param bool $value
	 * @param int $periodId
	 * @return $this
	 */
	public function setWithSubgroups( bool $value, int $periodId, int $subjectId );

	/**
	 *
	 * @param Subgroup $subgroup
	 * @return Subgroup
	 */
	public function saveSubgroup( Subgroup $subgroup );

	/**
	 *
	 * @param Subgroup[] $subgroups
	 * @return boolean
	 */
	public function saveSubgroups( array $subgroups );

	/**
	 *
	 * @param int|Subgroup $subgroup
	 * @return boolean
	 */
	public function deleteSubgroup( $subgroup );

	/**
	 *
	 * @param Subgroup[] $subgroups
	 * @return boolean
	 */
	public function deleteSubgroups( array $subgroups );

	/**
	 *
	 * @param Group $group
	 * @param int $periodId
	 * @param int $subjectId
	 * @return GroupsServiceInterface
	 */
	public function clearSubgroups( Group &$group, int $periodId, int $subjectId );
}
