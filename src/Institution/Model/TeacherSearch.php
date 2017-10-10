<?php
/**
 * Created by PhpStorm.
 * User: Romson
 * Date: 10.08.2016
 * Time: 16:39
 */

namespace Institution\Model;


use Application\Model\AbstractSearchModel;

class TeacherSearch extends AbstractSearchModel
{
    protected $fullname;
    protected $subjects;
    protected $email;
    protected $phone;
    protected $vacancy;
    protected $dismissed;
}