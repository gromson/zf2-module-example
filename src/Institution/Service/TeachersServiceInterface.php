<?php

namespace Institution\Service;

use Institution\Model\Teacher;
use Institution\Model\Subject;
use Institution\Model\TeacherSearch;

interface TeachersServiceInterface
{

    /**
     *
     * @param boolean $paginated
     *
     * @return Teacher[]|\Zend\Paginator\Paginator
     */
    public function fetch($paginated);

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Teacher|null
     */
    public function get($id, $secured, $permission);

    /**
     *
     * @param Teacher $teacher
     *
     * @return Teacher|false
     */
    public function save(Teacher $teacher);

    /**
     *
     * @param Teacher|int $teacher
     *
     * @return boolean
     */
    public function delete($teacher);

    /**
     *
     * @param Subject $subject
     *
     * @return Subject
     */
    public function addVacancy(Subject $subject);

    /**
     *
     * @param array|\Iterator|\IteratorAggregate|Teacher $teachers
     *
     * @return TeachersServiceInterface
     */
    public function appendSubjects(&$teachers);

    /**
     * @param TeacherSearch $search
     *
     * @return TeachersServiceInterface
     */
    public function setSearchModel(TeacherSearch $search);
}
