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

namespace Joomla\Component\Proofreader\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'typos';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link FilterInput::clean()}.
	 *
	 * @return  BaseController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
