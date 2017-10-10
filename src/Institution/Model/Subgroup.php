<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int $id
 * @property int $groups_id
 * @property int $subjects_id
 * @property int $periods_id
 * @property string $title
 */
class Subgroup extends AbstractModel
{

    protected $_columns = [
        'id',
        'groups_id',
        'subjects_id',
        'periods_id',
        'title'
    ];

}
