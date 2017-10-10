<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int $rooms_id
 * @property int $subjects_id
 *
 * Relations
 *
 * @property Room $room
 * @property Subject $subject
 */
class RoomSubject extends AbstractModel
{

	protected $_columns = [
		'rooms_id',
		'subjects_id'
	];

}
