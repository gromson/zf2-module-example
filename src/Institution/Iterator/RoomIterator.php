<?php
/**
 * Created by PhpStorm.
 * User: Роман
 * Date: 02.04.2016
 * Time: 19:39
 */

namespace Institution\Iterator;


use Application\Iterator\ModelIterator;
use Institution\Model\Room;
use Traversable;

class RoomIterator extends ModelIterator
{
    public function __construct( Traversable $data = null )
    {
        parent::__construct( $data, new Room(), 'id' );
    }
}