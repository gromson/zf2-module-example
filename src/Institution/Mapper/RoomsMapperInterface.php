<?php

namespace Institution\Mapper;

use Application\Model\SearchModelInterface;
use Institution\Iterator\RoomIterator;
use Institution\Model\Room;

interface RoomsMapperInterface
{

    /**
     * @param bool $withCategories
     *
     * @return RoomsMapperInterface
     */
    public function pullWithCategories( $withCategories = true );

    /**
     * @param int|null $scheduleId
     * @param bool     $withClassesNumbers
     *
     * @return $this
     */
    public function pullWithClassesNumbers(int $scheduleId = null, bool $withClassesNumbers = true);

    /**
     *
     * @param int     $accounts_id
     * @param boolean $paginated
     *
     * @return RoomIterator|\Zend\Paginator\Paginator
     */
    public function fetch( $accounts_id, $paginated );

    /**
     *
     * @param int $id
     *
     * @return Room
     */
    public function get( $id );

    /**
     *
     * @param Room $room
     *
     * @return Room|false
     */
    public function create( Room $room );

    /**
     *
     * @param Room $room
     *
     * @return Room
     */
    public function update( Room $room );

    /**
     *
     * @param int $id
     *
     * @return boolean
     */
    public function delete( $id );

    /**
     * @param SearchModelInterface $search
     *
     * @return RoomsMapperInterface
     */
    public function setSearchModel( SearchModelInterface $search );
}
