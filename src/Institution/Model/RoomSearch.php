<?php
/**
 * Created by PhpStorm.
 * User: Romson
 * Date: 10.08.2016
 * Time: 13:56
 */

namespace Institution\Model;

use Application\Model\AbstractSearchModel;

class RoomSearch extends AbstractSearchModel
{
    protected $number;
    protected $roomsCategoriesId;
    protected $subjects;
    protected $capacity;
    protected $comment;
}