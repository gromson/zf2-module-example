<?php

namespace Institution\Model;

use Application\Model\AbstractModel;
use Exception;

/**
 * @property int       $id
 * @property int       $accounts_id
 * @property int       $users_id
 * @property string    $firstname
 * @property string    $lastname
 * @property string    $middlename
 * @property array     $subjects_id
 * @property array     $school_level
 * @property boolean   $has_access
 * @property string    $email
 * @property string    $phone
 * @property boolean   $vacancy
 * @property boolean   $dismissed
 * @property string    $users_roles_code
 *
 * Relations
 *
 * @property Subject[] $subject
 */
class Teacher extends AbstractModel
{

    protected $_columns = [
        'id',
        'accounts_id',
        'users_id',
        'firstname',
        'lastname',
        'middlename',
        'school_level',
        'email',
        'phone',
        'vacancy',
        'dismissed'
    ];

    /**
     *
     * @var array
     */
    protected $subjects_id;

    /**
     * Boolean property indicating that new user should possibly be created
     *
     * @var bool
     */
    protected $has_access;

    /**
     * Property for creation of a new user
     *
     * @var string
     */
    protected $users_roles_code;

    /**
     *
     * @param array $values
     *
     * @return \Institution\Model\Teacher
     */
    public function setSubjectsId(array $values = null)
    {
        $this->subjects_id = $values;

        if (sizeof($this->subject) && sizeof($this->subjects_id)) {
            foreach ($this->subject as $key => $subject) {
                if (!in_array($subject->id, $this->subjects_id)) {
                    unset($this->_relations['subject'][$key]);
                }
            }
        } elseif ($this->subjects_id === null) {
            unset($this->_relations['subject']);
        }

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getSubjectsId()
    {
        if (!sizeof($this->subjects_id) && sizeof($this->subject)) {
            foreach ($this->subject as $subject) {
                $this->subjects_id[] = $subject->id;
            }
        }

        return $this->subjects_id;
    }

    /**
     *
     * @param bool $value
     *
     * @return \Institution\Model\Teacher
     */
    public function setHasAccess($value)
    {
        $this->has_access = (bool) $value;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getHasAccess()
    {
        return $this->has_access;
    }

    /**
     *
     * @return boolean
     */
    public function hasAccess()
    {
        if (gettype($this->has_access) !== 'boolean') {
            return !empty($this->users_id);
        } else {
            return $this->has_access;
        }
    }

    /**
     *
     * @param string $value
     *
     * @return \Institution\Model\Teacher
     */
    public function setUsersRolesCode($value)
    {
        $this->users_roles_code = $value;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUsersRolesCode()
    {
        return $this->users_roles_code;
    }

    /**
     *
     * @param string $format sprintf name format %1$s - first name, %2$s - last name, %3$s - middle name
     *
     * @return string
     */
    public function getFullName($format = '%2$s %1$s %3$s')
    {
        return sprintf($format, $this->firstname, $this->lastname, $this->middlename);
    }

    /**
     *
     * @return string
     */
    public function getShortName()
    {
        $firstname = strlen($this->firstname) ? mb_substr($this->firstname, 0, 1, 'utf-8') . '.' : '';
        $middlename = strlen($this->middlename) ? mb_substr($this->middlename, 0, 1, 'utf-8') . '.' : '';

        return sprintf('%s %s %s', $this->lastname, $firstname, $middlename);
    }

    /**
     *
     * @param array|string $value
     *
     * @throws \InvalidArgumentException
     */
    public function setSchoolLevel($value)
    {
        if (!is_array($value) && !is_string($value)) {
            throw new \InvalidArgumentException(
                sprintf('Teacher\'s property "school_level" must be an array or a string, %s given!', gettype($value))
            );
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $this->_properties['school_level'] = $value;
    }

    /**
     *
     * @return array
     */
    public function getSchoolLevel()
    {
        return explode(',', $this->_properties['school_level']);
    }

    /**
     *
     * @return boolean
     */
    public function getVacancy()
    {
        return (bool) $this->_properties['vacancy'];
    }

    /**
     * @param bool|int $value
     *
     * @throws Exception
     */
    public function setVacancy($value)
    {
        if (!is_numeric($value) && !is_bool($value)) {
            throw new Exception(
                sprintf('Teacher\'s property "vacancy" must be an integer or a boolean, %s given!', gettype($value))
            );
        }

        $this->_properties['vacancy'] = ($value === true || (int) $value !== 0 ? 1 : 0);
    }

}
