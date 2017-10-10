<?php
/**
 * Created by PhpStorm.
 * User: Роман
 * Date: 02.04.2016
 * Time: 19:30
 */

namespace Institution\Iterator;


use Application\Iterator\ModelIterator;
use Institution\Model\Subject;

class SubjectIterator extends ModelIterator
{
    public function __construct( $data = null )
    {
        parent::__construct( $data, new Subject() );
    }
}