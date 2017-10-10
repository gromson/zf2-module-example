<?php

namespace Institution\Mapper;

use Institution\Iterator\SubjectsProgramIterator;
use Institution\Model\Subject;
use Institution\Model\SubjectProgram;
use Application\Mapper\MapperException;

interface SubjectsProgramsMapperInterface
{
    /**
     *
     * @param int|array|\Traversable|Subject $subjects
     *
     * @return SubjectsProgramIterator
     */
    public function fetch( $subjects );

    /**
     *
     * @param int $id
     *
     * @return SubjectProgram
     */
    public function get( $id );

    /**
     *
     * @param SubjectProgram $subjectProgram
     *
     * @return SubjectProgram|false
     * @throws MapperException
     */
    public function create( SubjectProgram $subjectProgram );

    /**
     *
     * @param SubjectProgram $subjectProgram
     *
     * @return SubjectProgram
     * @throws MapperException
     */
    public function update( SubjectProgram $subjectProgram );

    /**
     *
     * @param int $id
     *
     * @return boolean
     */
    public function delete( $id );
}
