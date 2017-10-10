<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.01.17
 * Time: 16:52
 */

namespace Institution\Service;


use Institution\Model\Teacher;

interface TeachersDismissServiceInterface
{
    /**
     * @param Teacher $teacher
     * @param bool    $addVacancy
     *
     * @return Teacher
     */
    public function dismiss(Teacher $teacher, bool $addVacancy);
}