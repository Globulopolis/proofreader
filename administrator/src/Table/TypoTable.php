<?php
/**
 * Proofreader
 *
 * @package     Proofreader
 * @author      Sergey M. Litvinov (smart@joomlatune.com)
 * @copyright   Copyright (C) 2013-2015 by Sergey M. Litvinov. All rights reserved.
 * @copyright   Copyright (C) 2005-2007 by Alexandr Balashov. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Proofreader\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Typos table
 *
 * @package Proofreader
 * @since   2.0
 */
class TypoTable extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  3.10
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   2.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__proofreader_typos', 'id', $db);
	}
}
