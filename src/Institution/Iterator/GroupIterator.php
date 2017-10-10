<?php
/**
 * Created by PhpStorm.
 * User: Роман
 * Date: 03.04.2016
 * Time: 19:28
 */

namespace Institution\Iterator;


use Application\Iterator\ModelIterator;
use Institution\Model\Group;

class GroupIterator extends ModelIterator
{
    public function __construct( $data = null, Group $group = null )
    {
        parent::__construct( $data, $group );
    }
}