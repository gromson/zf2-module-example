<?php

namespace Institution\Service;

use Institution\Iterator\GroupIterator;
use Institution\Mapper\GroupsMapperInterface;
use Zend\Debug\Debug;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Exception\UnauthorizedException;
use Institution\Model\Group;
use Institution\Model\Subgroup;
use Institution\Model\Grade;
use Institution\Service\GradesServiceInterface;
use Authorization\Identity\Identity;
use Application\Exception\RuntimeException;
use Application\Service\Exception\ServiceException;

class GroupsService implements GroupsServiceInterface
{

    /**
     *
     * @var Identity
     */
    protected $identity;

    /**
     *
     * @var \Institution\Mapper\GroupsMapperInterface
     */
    protected $mapper;

    /**
     *
     * @var GradesServiceInterface
     */
    protected $gradesService;

    /**
     *
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * @var null|Group[]
     */
    protected $cachedGroups = null;

    /**
     *
     * @param int $level Grade level
     *
     * @return string
     */
    static public function getSchoolGradeCategory( int $level )
    {
        if ( $level <= self::ELEMENTARY_SCHOOL_HIGH_EDGE ) {
            return self::ELEMENTARY_SCHOOL;
        } elseif ( $level >= self::MIDDLE_SCHOOL_LOW_EDGE && $level <= self::MIDDLE_SCHOOL_HIGH_EDGE ) {
            return self::MIDDLE_SCHOOL;
        } elseif ( $level >= self::HIGH_SCHOOL_LOW_EDGE ) {
            return self::HIGH_SCHOOL;
        }
    }

    /**
     *
     * @param string $category GroupsServiceInterface::ELEMENTARY_SCHOOL, GroupsServiceInterface::MIDDLE_SCHOOL or
     *                         GroupsServiceInterface::HIGH_SCHOOL
     *
     * @return array
     */
    static public function getSchoolCategoryEdges( string $category )
    {
        $result = [
            'low'  => null,
            'high' => null
        ];

        switch ( $category ) {
            case self::ELEMENTARY_SCHOOL:
                $result['low'] = self::ELEMENTARY_SCHOOL_LOW_EDGE;
                $result['high'] = self::ELEMENTARY_SCHOOL_HIGH_EDGE;
                break;
            case self::MIDDLE_SCHOOL:
                $result['low'] = self::MIDDLE_SCHOOL_LOW_EDGE;
                $result['high'] = self::MIDDLE_SCHOOL_HIGH_EDGE;
                break;
            case self::HIGH_SCHOOL:
                $result['low'] = self::HIGH_SCHOOL_LOW_EDGE;
                $result['high'] = self::HIGH_SCHOOL_HIGH_EDGE;
                break;
        }

        return $result;
    }

    public function __construct(
        Identity $identity,
        GroupsMapperInterface $mapper,
        GradesServiceInterface $gradesService,
        AuthorizationServiceInterface $authorizationService
    )
    {
        $this->identity = $identity;
        $this->mapper = $mapper;
        $this->gradesService = $gradesService;
        $this->authorizationService = $authorizationService;
    }

    /**
     *
     * @param int         $state_year
     * @param null|string $category one of the self::*_SCHOOL constants
     *
     * @return GroupIterator
     */
    public function fetch( $state_year = null, string $category = null )
    {
        if ( !$this->cachedGroups ) {
            $this->cachedGroups = $this->mapper->fetch( $this->identity->getAccountId(), $state_year, false );
        }

        if ( $category ) {
            $groups = new GroupIterator();
            $edges = $this->getSchoolCategoryEdges( $category );
            $i = 0;

            $this->cachedGroups->rewind();

            foreach ( $this->cachedGroups as $group ) {
                if ( $group->getActiveGrade()->level >= $edges['low'] && $group->getActiveGrade()->level <= $edges['high'] ) {
                    if ( !$i++ ) {
                        $groups->setObjectPrototype( $group );
                    }
                    $groups->addItem( $group );
                }
            }
        } else {
            $groups = $this->cachedGroups;
        }

        return $groups;
    }

    /**
     *
     * @param int     $id
     * @param int     $state_year
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Group
     * @throws UnauthorizedException
     */
    public function get( $id, $state_year = null, $secured = true, $permission = 'groups.view' )
    {
        $group = $this->mapper->get( $id, $state_year );

        if ( $secured ) {
            if ( $this->authorizationService->isGranted( $permission, $group ) ) {
                return $group;
            } else {
                throw new UnauthorizedException();
            }
        }

        return $group;
    }

    /**
     *
     * @param Group $group
     *
     * @return Group
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    public function save( Group $group )
    {
        $this->beginTransaction();

        $grade = $group->getActiveGrade();
        $final = $group->getFinal();
        $levelUp = $group->getLevelUp();

        if ( $group->getIsNewRecord() && $this->authorizationService->isGranted( 'groups.management' ) ) {
            if ( $group->delete === false ) {
                $group->accounts_id = $this->identity->getAccountId();
                $group = $this->mapper->create( $group );
            } else {
                $group = true;
            }
        } elseif ( !$group->getIsNewRecord() && $this->authorizationService->isGranted( 'groups.management', $group ) ) {
            if ( $group->delete === true ) {
                $group = $this->delete( $group );
            } else {
                $group = $this->mapper->update( $group );
            }
        } else {
            $this->rollback();
            throw new UnauthorizedException();
        }

        if ( $group ) {
            if ( $group instanceof Group && $grade instanceof Grade ) {
                $grade->groups_id = $group->id;

                if ( $final ) {
                    $grade->final = 1;
                } elseif ( $levelUp ) {
                    $grade->level++;
                    $grade->begin_year++;
                }

                if ( !$this->gradesService->save( $grade, $group ) ) {
                    $this->rollback();
                    throw new ServiceException( 'Error occured while trying to save a grade!' );
                }

                $group->setActiveGrade( $grade );
            }

            $this->commit();

            return $group;
        } else {
            $this->rollback();
            throw new ServiceException( 'Error occured while trying to save a group!' );
        }
    }

    /**
     *
     * @param Group|int $group
     *
     * @return boolean
     * @throws \Exception
     * @throws UnauthorizedException
     */
    public function delete( $group )
    {
        $id = is_numeric( $group ) ? ( int ) $group : $group->id;

        if ( ( $group instanceof Group ) === false ) {
            $group = $this->get( $id, false );
        }

        if ( $this->authorizationService->isGranted( 'groups.management', $group ) ) {
            if ( !is_numeric( $group ) && !( $group instanceof Group ) ) {
                throw new RuntimeException( 'Wrong 1 argument given in GroupsService::delete(), argument must be an integer or an instance of \Institution\Model\Group!' );
            }

            return $this->mapper->delete( $id );
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     *
     * @param bool $value
     *
     * @return \Institution\Service\GroupsService
     */
    public function setWithActiveGradeOnly( bool $value )
    {
        $this->mapper->setWithActiveGradeOnly( $value );

        return $this;
    }

    /**
     *
     * @param bool $value
     * @param int  $periodId
     *
     * @return \Institution\Service\GroupsService
     */
    public function setWithSubgroups( bool $value, int $periodId = null, int $subjectId = null )
    {
        $this->mapper->setWithSubgroups( $value, $periodId, $subjectId );

        return $this;
    }

    /**
     *
     * @param Subgroup $subgroup
     *
     * @return Subgroup
     * @throws ServiceException
     * @throws UnauthorizedException
     */
    public function saveSubgroup( Subgroup $subgroup )
    {
        if ( $subgroup->getIsNewRecord() && $this->authorizationService->isGranted( 'curriculum.management' ) ) {
            $subgroup = $this->mapper->createSubgroup( $subgroup );
        } elseif ( !$subgroup->getIsNewRecord() && $this->authorizationService->isGranted( 'curriculum.management' ) ) {
            throw new ServiceException( 'Object must be a new record!' );
//            $subgroup = $this->mapper->update( $subgroup );
        } else {
            throw new UnauthorizedException();
        }

        if ( $subgroup ) {
            return $subgroup;
        } else {
            return false;
        }
    }

    /**
     *
     * @param Subgroup[] $subgroups
     *
     * @return boolean
     * @throws ServiceException
     */
    public function saveSubgroups( array $subgroups )
    {
        $this->beginTransaction();

        foreach ( $subgroups as $subgroup ) {
            if ( ( $subgroup instanceof Subgroup ) === false ) {
                $this->rollback();
                throw new ServiceException( sprintf( 'The element of input array must be an instance of Intitution\Model\Subgroups %s given', is_object( $subgroup ) ? get_class( $subgroup ) : gettype( $subgroup ) ) );
            }

            if ( !$this->saveSubgroup( $subgroup ) ) {
                $this->rollback();

                return false;
            }
        }

        $this->commit();

        return true;
    }

    public function deleteSubgroup( $subgroup )
    {
        $id = is_numeric( $subgroup ) ? ( int ) $subgroup : $subgroup->id;

        if ( $this->authorizationService->isGranted( 'curriculum.management' ) ) {
            if ( !is_numeric( $subgroup ) && !( $subgroup instanceof Subgroup ) ) {
                throw new RuntimeException( 'Wrong 1 argument given in GroupsService::deleteSubgroup(), argument must be an integer or an instance of \Institution\Model\Subgroup!' );
            }

            return $this->mapper->deleteSubgroup( $id );
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     *
     * @param Subgroup[] $subgroups
     *
     * @return boolean
     * @throws ServiceException
     */
    public function deleteSubgroups( array $subgroups )
    {
        $this->beginTransaction();

        foreach ( $subgroups as $subgroup ) {
            if ( ( $subgroup instanceof Subgroup ) === false ) {
                $this->rollback();
                throw new ServiceException( sprintf( 'The element of input array must be an instance of Intitution\Model\Subgroups %s given', is_object( $subgroup ) ? get_class( $subgroup ) : gettype( $subgroup ) ) );
            }

            if ( !$this->deleteSubgroup( $subgroup->id ) ) {
                $this->rollback();

                return false;
            }
        }

        $this->commit();

        return true;
    }

    /**
     *
     * @param Group $group
     * @param int   $periodId
     * @param int   $subjectId
     *
     * @return GroupsServiceInterface
     */
    public function clearSubgroups( Group &$group, int $periodId, int $subjectId )
    {
        $subgroups = $group->subgroup;

        if ( empty( $subgroups ) || !is_array( $subgroups ) ) {
            $subgroups = $this->mapper->getSubgroups( $group->id, $periodId, $subjectId );
        }

        if ( sizeof( $subgroups ) ) {
            $this->deleteSubgroups( $subgroups );
        }

        unset( $group->subgroup );

        return $this;
    }

    protected function beginTransaction()
    {
        if ( is_callable( [ $this->mapper, 'beginTransaction' ] ) ) {
            $this->mapper->beginTransaction();
        }
    }

    protected function commit()
    {
        if ( is_callable( [ $this->mapper, 'commit' ] ) ) {
            $this->mapper->commit();
        }
    }

    protected function rollback()
    {
        if ( is_callable( [ $this->mapper, 'rollback' ] ) ) {
            $this->mapper->rollback();
        }
    }

}
