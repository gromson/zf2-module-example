<?php

namespace Institution\Service;

use Institution\Mapper\RoomsCategoriesMapperInterface;
use Authorization\Identity\Identity;

class RoomsCategoriesService implements RoomsCategoriesServiceInterface
{

	/**
	 *
	 * @var Identity
	 */
	protected $identity;

	/**
	 *
	 * @var \Institution\Mapper\RoomsCategoriesMapperInterface;
	 */
	protected $mapper;

	public function __construct( Identity $identity, RoomsCategoriesMapperInterface $mapper )
	{
		$this->identity = $identity;
		$this->mapper = $mapper;
	}

	/**
	 * @return \Iterator|\Institution\Model\RoomCategory[]
	 */
	public function fetch()
	{
		return $this->mapper->fetch( $this->identity->getAccountId() );
	}

	/**
	 *
	 * @param boolean $withBlankValue whether to show a null value
	 * @param string $blankValueText
	 * @return array
	 */
	public function fetchForDropDown( $withBlankValue = false, $blankValueText = '- none -' )
	{
		$list = $this->mapper->fetch( $this->identity->getAccountId() );

		$resultArray = [ ];

		if ( $withBlankValue ) {
			$resultArray[ null ] = $blankValueText;
		}

		foreach ( $list as $category ) {
			$resultArray[ $category->id ] = $category->title;
		}

		return $resultArray;
	}

}
