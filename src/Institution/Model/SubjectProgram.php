<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int $id
 * @property int $subjects_id
 * @property string $title
 * @property int $duration
 * @property string $authors
 *
 * Relations
 *
 * @property Subject $subject
 */
class SubjectProgram extends AbstractModel
{

	protected $_columns = [
		'id',
		'subjects_id',
		'title',
		'duration',
		'authors'
	];

}
