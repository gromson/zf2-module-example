<?php

namespace Institution\Model;

use Application\Model\AbstractModel;
use Institution\Iterator\SubjectsProgramIterator;

/**
 * @property int           $id
 * @property int           $subject_areas_id
 * @property int           $accounts_id
 * @property int           $ancestor_id
 * @property string        $title
 * @property string        $title_short
 * @property int           $level
 * @property array         $school_level
 *
 * Relations
 *
 * @property SubjectArea[] $subjectArea
 * @property Teacher[]     $teacher
 */
class Subject extends AbstractModel
{

    protected $_columns = [
        'id',
        'subject_areas_id',
        'accounts_id',
        'ancestor_id',
        'title',
        'title_short',
        'level',
        'school_level'
    ];

    /**
     * @var int
     */
    protected $depended_teachers_id;

    /**
     * @var int
     */
    protected $dependedRoomsId;

    /**
     * @var Programs[]
     */
    protected $programs = [];

    /**
     *
     * @param array $data
     *
     * @return \Institution\Model\Subject
     */
    public function setSubjectProgram( array $data )
    {
        $this->exchangeRelations( [ 'Institution\Model\SubjectProgram' => $data ] );

        return $this;
    }

    public function getSubjectArea()
    {
        return $this->_relations['subjectArea'][0];
    }

    /**
     *
     * @param array|string $value
     *
     * @throws \InvalidArgumentException
     */
    public function setSchoolLevel( $value )
    {
        if(!$value){
            $value = '';
        }

        if ( !is_array( $value ) && !is_string( $value ) ) {
            throw new \InvalidArgumentException( sprintf( 'Subject\'s property "school_level" must be an array or a string, %s given!', gettype( $value ) ) );
        }

        if ( is_array( $value ) ) {
            $value = implode( ',', $value );
        }

        $this->_properties['school_level'] = $value;
    }

    /**
     *
     * @return array
     */
    public function getSchoolLevel()
    {
        return explode( ',', $this->_properties['school_level'] );
    }

    public function setDependedTeachersId( $value )
    {
        $this->depended_teachers_id = (int) $value;
    }

    public function getDependedTeachersId()
    {
        return $this->depended_teachers_id;
    }

    public function setDependedRoomsId( int $value )
    {
        $this->dependedRoomsId = $value;
    }

    public function getDependedRoomsId()
    {
        return $this->dependedRoomsId;
    }

    /**
     * @param SubjectsProgramIterator|array $programs
     *
     * @return $this
     */
    public function setPrograms( $programs )
    {
        $this->programs = [ ];

        foreach ( $programs as $program ) {
            if ( $program instanceof SubjectProgram ) {
                $this->appendProgram( $program );
            } else {
                $subjectProgram = new SubjectProgram( $program );

                if ( is_callable( [ $subjectProgram, 'setIsNewRecord' ] ) && is_callable( [ $subjectProgram, 'isPkSet' ] ) ) {
                    $subjectProgram->setIsNewRecord( !$subjectProgram->isPkSet() );
                }

                $this->appendProgram( $subjectProgram );
            }
        }

        return $this;
    }

    /**
     * @param SubjectProgram $program
     *
     * @return Subject
     */
    public function appendProgram( SubjectProgram $program )
    {
        $this->programs[] = $program;

        return $this;
    }

    /**
     * @return Programs[]
     */
    public function getPrograms()
    {
        return $this->programs;
    }

    /**
     * @param bool $withRelationsAsProperties
     *
     * @return array
     */
    public function getArrayCopy( $withRelationsAsProperties = false )
    {
        $data = parent::getArrayCopy( $withRelationsAsProperties );

        if ( $withRelationsAsProperties ) {
            $data['programs'] = array_map( function ( $p ) {
                return $p->getArrayCopy();
            }, $this->programs );
        }

        return $data;
    }

}
