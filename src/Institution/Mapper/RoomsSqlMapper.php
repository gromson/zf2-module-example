<?php

namespace Institution\Mapper;

use Application\Exception\InvalidArgumentException;
use Institution\Iterator\RoomIterator;
use Institution\Model\RoomSearch;
use Zend\Db\Adapter\AdapterInterface;
use Application\Hydrator\ClassPrefixArraySerializable;
use Application\Mapper\AbstractSqlMapper;
use Institution\Model\Room;
use Zend\Debug\Debug;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as PredicateExpression;

class RoomsSqlMapper extends AbstractSqlMapper implements RoomsMapperInterface
{

    protected $dbTable = 'rooms';
    protected $dbTableAlias = 'r';

    /**
     * @var Select
     */
    protected $select;

    /**
     * @var bool
     */
    protected $withCategories = false;

    /**
     * @var bool
     */
    protected $withClassesNumbers = false;

    /**
     * @var int|null
     */
    protected $scheduleId = null;

    public function __construct(
        AdapterInterface $dbAdapter,
        $dbTable = null,
        $dbTableAlias = null,
        ClassPrefixArraySerializable $hydrator = null
    )
    {
        parent::__construct($dbAdapter, $dbTable, $dbTableAlias, $hydrator);
        $this->setObjectPrototype(new Room());
        $this->setModelIteratorPrototype(new RoomIterator());
    }

    /**
     * @param bool $value
     *
     * @return RoomsSqlMapper
     */
    public function pullWithCategories($value = true)
    {
        if ($this->withCategories != $value) {
            $this->withCategories = $value;
            $this->select = null;
        }

        return $this;
    }

    /**
     * @param int|null $scheduleId
     * @param bool     $withClassesNumbers
     *
     * @return $this
     */
    public function pullWithClassesNumbers(int $scheduleId = null, bool $withClassesNumbers = true)
    {
        if ($this->withClassesNumbers !== $withClassesNumbers) {
            if ($withClassesNumbers === true && $scheduleId === null) {
                throw new InvalidArgumentException('If second parameter is true $scheduleId must be set, null given!');
            }
            $this->withClassesNumbers = $withClassesNumbers;
            $this->scheduleId = $scheduleId;
            $this->select = null;
        }

        return $this;
    }

    /**
     *
     * @param int $accounts_id
     * @param boolean $paginated
     *
     * @return RoomIterator|\Zend\Paginator\Paginator
     */
    public function fetch($accounts_id, $paginated)
    {
        $where = new Where();
        $where->equalTo($this->dbTableAlias . '.accounts_id', $accounts_id)
            ->equalTo($this->dbTableAlias . '.deleted', 0);

        $searchWhere = $this->defineSearchCriteria();
        $where->addPredicate($searchWhere);

        return $this->fetchAbstractModelInterafce($where, $paginated);
    }

    /**
     *
     * @param int $id
     *
     * @return Room|null
     */
    public function get($id)
    {
        return $this->getModelInterface($id);
    }

    /**
     *
     * @param Room $room
     *
     * @return Room|null
     * @throws \Application\Mapper\MapperException
     */
    public function create(Room $room)
    {
        $subjects_id = $room->subjects_id;

        if ($room = $this->createModelInterface($room)) {
            $this->updateRoomsSubjects($room, $subjects_id);
        }

        return $room;
    }

    /**
     *
     * @param Room $room
     *
     * @return Room
     * @throws \Application\Mapper\MapperException
     */
    public function update(Room $room)
    {
        $subjects_id = $room->subjects_id;

        if ($room = $this->updateModelInterface($room)) {
            $this->updateRoomsSubjects($room, $subjects_id);
        }

        return $room;
    }

    public function updateRoomsSubjects(Room $room, array $subjects_id = null)
    {
        $delete = $this->getSql(false, true)->delete('rooms_subjects')->where(['rooms_id' => $room->id]);
        $stDelete = $this->getSql()->prepareStatementForSqlObject($delete);
        $stDelete->execute();

        if (sizeof($subjects_id)) {
            $sqlString = 'INSERT INTO `rooms_subjects` (`rooms_id`, `subjects_id`) VALUES (' . ((int) $room->id) . ',' . implode(
                    '),(' . ((int) $room->id) . ',',
                    $subjects_id
                ) . ')';
            $this->dbAdapter->query($sqlString, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }

    /**
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->deleteModelInterface($id);
    }

    protected function getSelect()
    {
        if (!$this->select instanceof Select) {
            $this->select = $this->getSql()->select();

            if ($this->withCategories) {
                $this->select->join(
                    ['rc' => 'rooms_categories'],
                    new Expression('rc.id=' . $this->dbTableAlias . '.rooms_categories_id AND rc.deleted = 0'),
                    [
                        'Institution\Model\RoomCategory_id' => 'id',
                        'Institution\Model\RoomCategory_accounts_id' => 'accounts_id',
                        'Institution\Model\RoomCategory_title' => 'title',
                        'Institution\Model\RoomCategory_sort' => 'sort',
                    ],
                    Select::JOIN_LEFT
                );
            }

            if ($this->withClassesNumbers) {
                $this->select->join(
                    ['c' => 'classes'],
                    new Expression(
                        'c.rooms_id=' . $this->dbTableAlias . '.id AND c.schedule_id = ?', $this->scheduleId
                    ),
                    [
                        'Schedule\Model\Classes_classes_numbers_id' => 'classes_numbers_id',
                        'Schedule\Model\Classes_day' => 'day'
                    ],
                    Select::JOIN_LEFT
                );
            }

            $this->select->order(
                [
                    new Expression('cast(' . $this->dbTableAlias . '.`number` as unsigned) ASC'),
                    $this->dbTableAlias . '.id ASC'
                ]
            );
        }

        return $this->select;
    }

    protected function getSelectCount($select)
    {
        $selectNew = clone $select;
        $selectNew->reset(Select::LIMIT);
        $selectNew->reset(Select::OFFSET);
        $selectNew->reset(Select::ORDER);
        //$selectNew->reset( Select::JOINS );
        $selectNew->group($this->dbTableAlias . '.id');

        $countSelect = new Select;

        $countSelect->columns([DbSelect::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $selectNew]);

        return $countSelect;
    }

    protected function defineSearchCriteria()
    {
        $where = new Where();
        $where->addPredicate(new PredicateExpression('1'));

        if ($this->searchModel instanceof RoomSearch) {
            if ($this->searchModel->number) {
                $where->equalTo($this->dbTableAlias . '.number', $this->searchModel->number);
            }

            if ($this->searchModel->roomsCategoriesId) {
                $where->equalTo($this->dbTableAlias . '.rooms_categories_id', $this->searchModel->roomsCategoriesId);
            }

            if ($this->searchModel->subjects) {
                $this->getSelect()->join(
                    ['rs' => 'rooms_subjects'],
                    'rs.rooms_id = ' . $this->dbTableAlias . '.id',
                    [],
                    Select::JOIN_INNER
                )->join(
                    ['s' => 'subjects'],
                    new Expression('s.id = rs.subjects_id AND s.deleted = 0'),
                    [],
                    Select::JOIN_INNER
                );
                $where->like('s.title', '%' . $this->searchModel->subjects . '%');
            }

            if ($this->searchModel->capacity) {
                $where->equalTo($this->dbTableAlias . '.capacity', $this->searchModel->capacity);
            }

            if ($this->searchModel->comment) {
                $where->like($this->dbTableAlias . '.comment', '%' . $this->searchModel->comment . '%');
            }
        }

        return $where;
    }

}
