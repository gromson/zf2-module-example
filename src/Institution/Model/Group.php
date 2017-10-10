<?php

namespace Institution\Model;

use Application\Model\AbstractModel;
use Application\Exception\RuntimeException;

/**
 * @property int        $id
 * @property int        $accounts_id
 * @property string     $letter
 * @property string     $title
 * @property string     $note
 *
 * Relations
 *
 * @property Grade[]    $grade
 * @property Subgroup[] $subgroup
 *
 */
class Group extends AbstractModel
{

    /**
     *
     * @var array
     */
    protected $_columns = [
        'id',
        'accounts_id',
        'letter',
        'title',
        'note'
    ];

    /**
     * Day and month of the beginnig of the academic year. Formatted like 'two_digits_month-two_digits_day'
     *
     * @var string
     */
    protected $academicYearBegin;

    /**
     * Day and month of the end of the academic year. Formatted like 'two_digits_month-two_digits_day'
     *
     * @var string
     */
    protected $academicYearEnd;

    /**
     *    A year of the beginnig of the academic year
     *
     * @var int
     */
    protected $stateYear;

    /**
     * Grade object for the set year
     *
     * @var Grade
     */
    protected $activeGrade;

    /**
     * Whether to create a new instance of Grade based on properties of the current activeGrade while saving an object
     *
     * @var boolean
     */
    protected $levelUp = false;

    /**
     * Whether to set activeGrade as final while saving a group
     *
     * @var boolean
     */
    protected $final = false;

    /**
     * Whether to delete group while saving
     *
     * @var boolean
     */
    protected $delete = false;

    public function __construct( $academicYearBegin, $academicYearEnd, array $data = null, $isNewRecord = true, $stateYear = null )
    {
        $this->setAcademicYearBegin( $academicYearBegin );
        $this->setAcademicYearEnd( $academicYearEnd );

        parent::__construct( $data, $isNewRecord );

        $this->setStateYear( $stateYear );
    }

    /**
     * Set the date of the beginning of the academic year
     *
     * @param string $value
     *
     * @return \Institution\Model\Group
     * @throws RuntimeException
     */
    public function setAcademicYearBegin( $value )
    {
        $value = $this->convertAcademicYearFromDate( $value );

        if ( !$this->isAcademicYearValid( $value ) ) {
            throw new RuntimeException( 'Wrong academic year string is given, the format should be \'month-day\' and match the pattern /^\d{2}-\d{2}$/' );
        }

        $this->academicYearBegin = $value;

        return $this;
    }

    /**
     * Set the date of the ending of the academic year
     *
     * @param string $value
     *
     * @return \Institution\Model\Group
     * @throws RuntimeException
     */
    public function setAcademicYearEnd( $value )
    {
        $value = $this->convertAcademicYearFromDate( $value );

        if ( !$this->isAcademicYearValid( $value ) ) {
            throw new RuntimeException( 'Wrong academic year string is given, the format should be \'month-day\' and match the pattern /^\d{2}-\d{2}$/' );
        }

        $this->academicYearEnd = $value;

        return $this;
    }

    /**
     * Get the date of the beginning of the academic year
     *
     * @return string
     */
    public function getAcademicYearBegin()
    {
        return $this->academicYearBegin;
    }

    /**
     * Get the date of the ending of the academic year
     *
     * @return type
     */
    public function getAcademicYearEnd()
    {
        return $this->academicYearEnd;
    }

    public function setStateYear( $stateYear = null )
    {
        if ( !$stateYear ) {
            $currentDate = date( 'Y-m-d' );

            if ( $currentDate >= date( 'Y-' ) . $this->getAcademicYearBegin() ) {
                $stateYear = (int) date( 'Y' );
            } else {
                $stateYear = (int) date( 'Y' ) - 1;
            }
        }

        $this->stateYear = (int) $stateYear;
        $this->defineActiveGrade();

        return $this;
    }

    public function getStateYear()
    {
        return $this->stateYear;
    }

    /**
     *
     * @return Grade
     */
    public function getActiveGrade()
    {
        if ( ( $this->activeGrade instanceof Grade ) === false || (int) $this->activeGrade->begin_year !== $this->stateYear ) {
            $this->defineActiveGrade();
        }

        return $this->activeGrade;
    }

    /**
     * @return string
     */
    public function getActiveGradeTitle(  )
    {
        if($this->getActiveGrade() instanceof Grade){
            return $this->getActiveGrade()->level . $this->letter;
        }else{
            return '';
        }
    }

    /**
     *
     * @return boolean
     */
    public function getLevelUp()
    {
        return $this->levelUp;
    }

    /**
     *
     * @param boolean $value
     *
     * @return Group
     */
    public function setLevelUp( $value )
    {
        $this->levelUp = (bool) $value;

        return $this;
    }

    /**
     *
     * @param boolean $value
     *
     * @return Group
     */
    public function setFinal( $value )
    {
        $this->final = (bool) $value;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getFinal()
    {
        return $this->final;
    }

    /**
     *
     * @param boolean $value
     * @return Group
     */
    public function setDelete( $value )
    {
        $this->delete = (bool) $value;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     *
     * @param \Institution\Model\Grade|array $grade
     *
     * @return \Institution\Model\Group
     * @throws RuntimeException
     */
    public function setActiveGrade( $grade )
    {
        if ( is_array( $grade ) ) {
            $data = $grade;
            $grade = new Grade();
            $grade->exchangeArray( $data );
        } elseif ( ( $grade instanceof Grade ) === false ) {
            throw new RuntimeException( sprintf( 'Argument must be an array or a Grade instance, %s given', ( is_object( $grade ) ? get_class( $grade ) : gettype( $grade ) ) ) );
        }
        $this->activeGrade = $grade;

        return $this;
    }

    public function getArrayCopy( $withRelationsAsProperties = false )
    {
        $arrayCopy = parent::getArrayCopy( $withRelationsAsProperties );
        if ( $withRelationsAsProperties && $this->getActiveGrade() ) {
            $arrayCopy['activeGrade'] = $this->getActiveGrade()->getArrayCopy();
        }

        return $arrayCopy;
    }

    protected function convertAcademicYearFromDate( $value )
    {
        $matches = [ ];

        if ( preg_match( '/^\d{4}-(\d{2}-\d{2})$/', $value, $matches ) ) {
            $value = $matches[1];
        }

        return $value;
    }

    protected function isAcademicYearValid( $value )
    {
        if ( preg_match( '/^\d{2}-\d{2}$/', $value ) ) {
            return true;
        }

        return false;
    }

    protected function defineActiveGrade()
    {
        if ( is_array( $this->grade ) ) {
            foreach ( $this->grade as $grade ) {
                if ( (int) $grade->begin_year === (int) $this->stateYear ) {
                    $this->activeGrade = $grade;

                    return;
                }
            }

//            foreach ( $this->grade as $grade ) {
//                if ( (int) $grade->begin_year === (int) $this->stateYear - 1 && (int) $grade->final === 0 ) {
//                    $this->activeGrade = $grade;
//                    $this->activeGrade->isLastYear = true;
//                    return;
//                }
//            }
        }
    }

}
