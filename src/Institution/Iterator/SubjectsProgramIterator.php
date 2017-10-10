<?php
/**
 * Created by PhpStorm.
 * User: Роман
 * Date: 03.04.2016
 * Time: 12:30
 */

namespace Institution\Iterator;

use Application\Iterator\ModelIterator;
use Institution\Model\SubjectProgram;

class SubjectsProgramIterator extends ModelIterator
{
    public function __construct( \Traversable $data )
    {
        parent::__construct( $data, new SubjectProgram() );
    }
}