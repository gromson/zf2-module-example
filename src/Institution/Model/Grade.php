<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int $groups_id
 * @property int $begin_year
 * @property int $level
 * @property int $student_count
 * @property int $male_count
 * @property int $female_count
 * @property int $final
 */
class Grade extends AbstractModel
{

	/**
	 *
	 * @var array
	 */
	protected $_columns = [
		'groups_id',
		'begin_year',
		'level',
		'students_count',
		'male_count',
		'female_count',
        'final'
	];

	/**
	 * This property shows whether the group has defined level in the previous academic year
	 *
	 * @var boolean
	 */
	protected $isLastYear = false;

	public function getPk()
	{
		$pk = [ ];

		if ( $this->groups_id && $this->begin_year ) {
			$pk = [
				'groups_id' => $this->groups_id,
				'begin_year' => $this->begin_year
			];
		}

		return $pk;
	}

	/**
	 *
	 * @param boolean $value
	 * @return \Institution\Model\Grade
	 */
	public function setIsLastYear( $value )
	{
		$this->isLastYear = ( bool ) $value;
		return $this;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getIsLastYear()
	{
		return $this->isLastYear;
	}

	/**
	 *
	 * @param bool $withRelationsAsProperties
	 */
	public function getArrayCopy( bool $withRelationsAsProperties = false )
	{
		$array = parent::getArrayCopy( $withRelationsAsProperties );
		$array[ 'isLastYear' ] = $this->isLastYear;
		return $array;
	}

}
