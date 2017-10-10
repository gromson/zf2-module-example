<?php

namespace Institution\Mapper;

use Application\Model\SearchModelInterface;
use Institution\Model\Teacher;
use Institution\Model\Subject;

interface TeachersMapperInterface
{

    /**
     *
     * @param int $accounts_id
     * @param boolean $paginated
     * @return Teacher[]|\Zend\Paginator\Paginator
     */
    public function fetch( $accounts_id, $paginated );

    /**
     *
     * @param int $id
     * @return Teacher
     */
    public function get( $id );

    /**
     *
     * @param Teacher $teacher
     * @return Teacher|false
     */
    public function create( Teacher $teacher );

    /**
     *
     * @param Teacher $teacher
     * @return Teacher
     */
    public function update( Teacher $teacher );

    /**
     *
     * @param int $id
     * @return boolean
     */
    public function delete( $id );

    /**
     * 
     * @param Subject $subject
     * @return Teacher
     */
    public function addVacancy( Subject $subject );

    /**
     * 
     * @param \Zend\Db\Sql\Select|array|\Iterator|\IteratorAggregate|Teacher $teachers
     * @return TeachersMapperInterface
     */
    public function appendSubjects( &$teachers );

    /**
     * @param SearchModelInterface $search
     *
     * @return TeachersMapperInterface
     */
    public function setSearchModel( SearchModelInterface $search );
}
