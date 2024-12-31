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

namespace Joomla\Component\Proofreader\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;

/**
 * Routing class from com_proofreader
 *
 * @since  3.0
 */
class Router extends RouterBase
{
	/**
	 * Build the route for the com_proofreader component
	 *
	 * @param   array  $query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   2.0
	 */
	public function build(&$query)
	{
		$segments = array();

		if (isset($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		// Required! Formats other than html will lead to error in FormHelper::getFormScripts()
		if (isset($query['format']))
		{
			unset($query['format']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   2.0
	 */
	public function parse(&$segments)
	{
		$vars = array();

		if (count($segments))
		{
			$segment = array_shift($segments);

			if (!is_numeric($segment))
			{
				$vars['task'] = $segment;
			}
		}

		return $vars;
	}
}
