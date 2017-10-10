<?php

namespace Institution\Service;

use Institution\Iterator\RoomIterator;
use Institution\Model\Room;
use Institution\Model\RoomSearch;

interface RoomsServiceInterface
{
    /**
     * @param bool $withCategories
     *
     * @return RoomsServiceInterface
     */
    public function pullWithCategories( bool $withCategories = true );

    /**
     * @param int|null $scheduleId
     * @param bool     $withClassesNumbers
     *
     * @return $this
     */
    public function pullWithClassesNumbers( int $scheduleId = null, $withClassesNumbers = true );

    /**
     * @param bool $withSubjects
     *
     * @return RoomsServiceInterface
     */
    public function pullWithSubjects( bool $withSubjects = true );
    
	/**
	 *
	 * @param bool $paginated
	 * @return RoomIterator|\Zend\Paginator\Paginator
	 */
	public function fetch( bool $paginated = false );

	/**
	 *
	 * @param int $id
	 * @param boolean $secured Whether to check permission with assertion
	 * @param string $permission Permission code
	 * @return Room|null
	 */
	public function get( $id, $secured, $permission );

	/**
	 *
	 * @param Room $room
	 * @return Room|false
	 */
	public function save( Room $room );

	/**
	 *
	 * @param Room|int $room
	 * @return boolean
	 */
	public function delete( $room );

	/**
	 * @param array|\Traversable $rooms
	 */
	public function appendSubjects( &$rooms );

    /**
     * @param RoomSearch $search
     *
     * @return RoomsServiceInterface
     */
    public function setSearchModel( RoomSearch $search );
}
