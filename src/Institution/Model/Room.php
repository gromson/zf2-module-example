<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int          $id
 * @property int          $accounts_id
 * @property int          $rooms_categoties_id
 * @property string       $number
 * @property string       $comment
 * @property int          $capacity
 * @property array        $subjects_id
 *
 * Relations
 *
 * @property RoomCategory $roomCategory
 *
 */
class Room extends AbstractModel
{

    protected $_columns = [
        'id',
        'accounts_id',
        'rooms_categories_id',
        'number',
        'comment',
        'capacity'
    ];

    /**
     *
     * @var array
     */
    protected $subjects_id = null;

    /**
     * @var Subject[]
     */
    protected $subjects = [ ];

    public function setSubjectsId( array $values = null )
    {
        $this->subjects_id = $values;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getSubjectsId()
    {
        if ( /*!sizeof( $this->subjects_id )*/ $this->subjects_id === null && sizeof( $this->subjects ) ) {
            foreach ( $this->subjects as $subject ) {
                $this->subjects_id[] = $subject->id;
            }
        }

        return $this->subjects_id;
    }

    public function getRoomCategory()
    {
        return $this->_relations['roomCategory'][0];
    }

    /**
     * @param Subject $subject
     *
     * @return Room
     */
    public function appendSubject( Subject $subject )
    {
        $this->subjects[] = $subject;

        return $this;
    }

    /**
     * @return Subject[]
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

}
