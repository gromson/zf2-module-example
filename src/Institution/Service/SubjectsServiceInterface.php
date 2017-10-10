<?php

namespace Institution\Service;

use Institution\Iterator\SubjectIterator;
use Institution\Model\Subject;
use Institution\Model\SubjectProgram;
use Institution\Model\SubjectSearch;

interface SubjectsServiceInterface
{

    /**
     *
     * @param boolean $paginated
     * @param string  $category one of the GroupsServiceInterface::*_SCHOOL constants
     * @param int     $orderBy
     *
     * @return SubjectIterator|\Zend\Paginator\Paginator
     */
    public function fetch( $paginated = false, string $category = null, int $orderBy );

    /**
     * @param array $roomIds
     *
     * @return SubjectIterator|array
     */
    public function fetchForRooms( $roomIds );

    /**
     * @param boolean $withBlankValue whether to show a null value
     * @param string  $blankValueText
     *
     * @return array
     */
    public function fetchForDropDown( $withBlankValue, $blankValueText );

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Subject|null
     */
    public function get( $id, $secured, $permission );

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     * @param Subject $subject
     *
     * @return SubjectProgram|null
     */
    public function getProgram( $id, $secured, $permission, Subject $subject );

    /**
     *
     * @param Subject $subject
     *
     * @return Subject|false
     */
    public function save( Subject $subject );

    /**
     *
     * @param Subject|int $subject
     *
     * @return boolean
     */
    public function delete( $subject );

    /**
     * Deactivate subject for the given year
     *
     * @param int|Subject $subject
     *
     * @return boolean
     */
    public function deactivate( $subject );

    /**
     *
     * @param \Institution\Model\SubjectProgram|int $program
     * @param Subject                               $subject
     *
     * @return boolean
     */
    public function deleteProgram( $program, Subject $subject );

    /**
     * Whether to get the subjects with the relations Institution\Model\Teacher
     *
     * @param bool $value
     *
     * @return $this
     */
    public function getWithTeachers( bool $value );

    /**
     * Old style.
     * Whether to get the subjects with the relations Institution\Model\SubjectProgram
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setWithSubjectPrograms( bool $value );

    /**
     * New style
     *
     * @param bool $value
     *
     * @return SubjectsServiceInterface
     */
    public function getWithPrograms( bool $value = true );

    /**
     * @param Subject|array|\Traversable $subjects
     */
    public function appendPrograms( &$subjects );

    /**
     * @param bool $value
     * @param int  $periodsId
     *
     * @return SubjectsServiceInterface
     */
    public function getWithCurriculum( bool $value = true, int $periodsId );

    /**
     * @return \Institution\Model\SubjectArea[]
     */
    public function fetchAreas();

    /**
     * @return array
     */
    public function fetchAreasForDropDown();

    /**
     * @param SubjectSearch $search
     *
     * @return SubjectsServiceInterface
     */
    public function setSearchModel( SubjectSearch $search );
}
