<?php

namespace Institution\Service;

interface RoomsCategoriesServiceInterface
{

	/**
	 * @return \Iterator|\Institution\Model\RoomCategory[]
	 */
	public function fetch();

	/**
	 * @param boolean $withBlankValue whether to show a null value
	 * @param string $blankValueText
	 * @return array
	 */
	public function fetchForDropDown( $withBlankValue, $blankValueText );
}
