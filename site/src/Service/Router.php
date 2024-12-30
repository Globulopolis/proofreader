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
			switch ($query['task'])
			{
				case 'typo.submit':
					$segments[] = 'submit';
					break;

				case 'typo.form':
					$segments[] = 'form';
					break;

				default:
					$segments[] = $query['task'];
					break;
			}

			unset($query['task']);
		}

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
			switch ($segments[0])
			{
				case 'submit':
					$vars['task']   = 'typo.submit';
					break;

				case 'form':
					$vars['task']   = 'typo.form';
					break;

				default:
					$vars['task'] = $segments[0];
					break;
			}

			unset($segments[0]);
		}

		return $vars;
	}
}
