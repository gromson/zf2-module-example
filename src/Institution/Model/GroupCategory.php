<?php

namespace Institution\Model;

use Application\Model\AbstractModel;

/**
 * @property int $id
 * @property int $accounts_id
 * @property string $title
 */

class GroupCategory extends AbstractModel
{
	protected $_columns = [
		'id',
		'accounts_id',
		'title'
		];
}
