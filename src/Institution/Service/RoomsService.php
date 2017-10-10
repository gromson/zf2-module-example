<?php

namespace Institution\Service;

use Institution\Iterator\RoomIterator;
use Institution\Iterator\SubjectIterator;
use Institution\Mapper\RoomsMapperInterface;
use Institution\Model\RoomSearch;
use Zend\Debug\Debug;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Exception\UnauthorizedException;
use Institution\Model\Room;
use Authorization\Identity\Identity;
use Application\Exception\RuntimeException;
use Application\Service\Exception\ServiceException;

class RoomsService implements RoomsServiceInterface
{

    /**
     *
     * @var Identity
     */
    protected $identity;

    /**
     *
     * @var \Institution\Mapper\RoomsMapperInterface
     */
    protected $mapper;

    /**
     * @var SubjectsServiceInterface
     */
    protected $subjectService;

    /**
     *
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * @var bool
     */
    protected $withCategories = false;

    /**
     * @var bool
     */
    protected $withSubjects = false;

    public function __construct(
        Identity $identity,
        RoomsMapperInterface $mapper,
        SubjectsServiceInterface $subjectsService,
        AuthorizationServiceInterface $authorizationService
    )
    {
        $this->identity = $identity;
        $this->mapper = $mapper;
        $this->subjectService = $subjectsService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param bool $withCategories
     *
     * @return RoomsService
     */
    public function pullWithCategories(bool $withCategories = true)
    {
        $this->mapper->pullWithCategories($withCategories);

        return $this;
    }

    /**
     * @param int|null $scheduleId
     * @param bool     $withClassesNumbers
     *
     * @return $this
     */
    public function pullWithClassesNumbers(int $scheduleId = null, $withClassesNumbers = true)
    {
        $this->mapper->pullWithClassesNumbers($scheduleId, $withClassesNumbers);

        return $this;
    }


    /**
     * @param bool $withSubjects
     *
     * @return RoomsService
     */
    public function pullWithSubjects(bool $withSubjects = true)
    {
        $this->withSubjects = $withSubjects;

        return $this;
    }

    /**
     *
     * @param bool $paginated
     *
     * @throws UnauthorizedException
     * @return RoomIterator|\Zend\Paginator\Paginator
     */
    public function fetch(bool $paginated = false)
    {
        if ($this->withCategories) {
            $this->mapper->pullWithCategories();
        }

        $rooms = $this->mapper->fetch($this->identity->getAccountId(), $paginated);

        if ($this->withSubjects && !$paginated) {
            $this->appendSubjects($rooms);
        }

        return $rooms;

    }

    /**
     *
     * @param int     $id
     * @param boolean $secured    Whether to check permission with assertion
     * @param string  $permission Permission code
     *
     * @return Room
     * @throws UnauthorizedException
     */
    public function get($id, $secured = true, $permission = 'rooms.view')
    {
        if ($this->withCategories) {
            $this->mapper->pullWithCategories();
        }

        $room = $this->mapper->get($id);

        if ($this->withSubjects) {
            $this->appendSubjects($room);
        }

        if ($secured) {
            if ($this->authorizationService->isGranted($permission, $room)) {
                return $room;
            } else {
                throw new UnauthorizedException();
            }
        }

        return $room;
    }

    /**
     *
     * @param Room $room
     *
     * @return Room|false
     * @throws UnauthorizedException
     * @throws ServiceException
     */
    public function save(Room $room)
    {
        $this->beginTransaction();

        if ($room->getIsNewRecord() && $this->authorizationService->isGranted('rooms.create')) {
            $room = $this->mapper->create($room);
        } elseif (!$room->getIsNewRecord() && $this->authorizationService->isGranted('rooms.update', $room)) {
            $room = $this->mapper->update($room);
        } else {
            throw new UnauthorizedException();
        }

        if ($room) {
            $this->commit();

            return $room;
        } else {
            $this->rollback();
            throw new ServiceException('Error occurred while trying to save a room!');
        }
    }

    /**
     *
     * @param Room|int $room
     *
     * @return boolean
     * @throws \Exception
     * @throws UnauthorizedException
     */
    public function delete($room)
    {
        $id = is_numeric($room) ? ( int ) $room : $room->id;

        if (($room instanceof Room) === false) {
            $room = $this->get($id, false);
        }

        if ($this->authorizationService->isGranted('rooms.delete', $room)) {
            if (!is_numeric($room) && !($room instanceof Room)) {
                throw new RuntimeException(
                    'Wrong 1 argument given in RoomsService::delete(), argument must be an integer or an instance of \Institution\Model\Room!'
                );
            }

            return $this->mapper->delete($id);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     * @param array|\Traversable|Room $rooms
     */
    public function appendSubjects(&$rooms)
    {
        $ids = [];

        if ($rooms instanceof Room) {
            $ids = [$rooms->id];
        } else {
            foreach ($rooms as $room) {
                $ids[] = (int) $room->id;
            }
        }

        $subjects = $this->subjectService->fetchForRooms($ids);

        if ($rooms instanceof Room) {
            $this->appendSubjectsToRoom($rooms, $subjects);
        } else {
            foreach ($rooms as &$room) {
                $this->appendSubjectsToRoom($room, $subjects);
            }
        }
    }

    /**
     * @param RoomSearch $search
     *
     * @return RoomsService
     */
    public function setSearchModel(RoomSearch $search)
    {
        $this->mapper->setSearchModel($search);

        return $this;
    }

    /**
     * @param Room                  $room
     * @param array|SubjectIterator $subjects
     */
    protected function appendSubjectsToRoom(&$room, $subjects)
    {
        foreach ($subjects as $subject) {
            if ($subject->getDependedRoomsId() === (int) $room->id) {
                $room->appendSubject($subject);
            }
        }
    }

    protected function beginTransaction()
    {
        if (is_callable([$this->mapper, 'beginTransaction'])) {
            $this->mapper->beginTransaction();
        }
    }

    protected function commit()
    {
        if (is_callable([$this->mapper, 'commit'])) {
            $this->mapper->commit();
        }
    }

    protected function rollback()
    {
        if (is_callable([$this->mapper, 'rollback'])) {
            $this->mapper->rollback();
        }
    }

}
