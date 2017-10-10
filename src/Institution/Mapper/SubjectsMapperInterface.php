<?php

namespace Institution\Mapper;

use Application\Model\SearchModelInterface;
use Institution\Iterator\SubjectIterator;
use Institution\Model\Subject;
use Application\Mapper\MapperException;

interface SubjectsMapperInterface
{

    /**
     *
     * @param int     $accounts_id
     * @param int     $beginYear
     * @param boolean $paginated
     * @param int     $orderBy
     *
     * @return SubjectIterator|\Zend\Paginator\Paginator
     */
    public function fetch( int $accounts_id, int $beginYear, bool $paginated, int $orderBy );

    /**
     * @param array $roomIds
     * @param int   $beginYear
     *
     * @return SubjectIterator|bool
     */
    public function fetchForRooms( array $roomIds, int $beginYear );

    /**
     *
     * @param int $id
     *
     * @return Subject
     */
    public function get( $id );

    /**
     *
     * @param Subject $subject
     * @param int     $beginYear
     *
     * @return Subject|false
     * @throws MapperException
     */
    public function create( Subject $subject, int $beginYear );

    /**
     *
     * @param Subject $subject
     *
     * @return Subject
     * @throws MapperException
     */
    public function update( Subject $subject );

    /**
     *
     * @param int $id
     *
     * @return boolean
     */
    public function delete( $id );

    /**
     *
     * @param int $id
     * @param int $beginYear
     *
     * @return boolean
     */
    public function removeBeginYear( int $id, int $beginYear );

    /**
     *
     * @param bool $value
     */
    public function setWithTeachers( bool $value );

    /**
     * @return bool
     */
    public function getWithTeachers();

    /**
     * @param bool $value
     * @param int  $periodsId
     */
    public function setWithCurriculum( bool $value, int $periodsId );

    /**
     * @return bool
     */
    public function getWithCurriculum();

    /**
     * @return \Institution\Model\SubjectArea[]
     */
    public function fetchAreas();

    /**
     * @param SearchModelInterface $search
     *
     * @return SubjectsMapperInterface
     */
    public function setSearchModel( SearchModelInterface $search );
}
