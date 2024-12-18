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

namespace Joomla\Plugin\Quickicon\Proofreader\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Displays an icon for quick access to typos in dashboard
 *
 * @since  4.1
 */
final class Quickicon extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Show jcomments icon.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   3.9.0
	 */
	public function onGetIcons(string $context): array
	{
		$app = $this->getApplication() ?: $this->app;

		if ($context !== $this->params->get('context', 'mod_quickicon')
			|| !$app->getIdentity()->authorise('core.manage', 'com_proofreader'))
		{
			return array();
		}

		$text = $this->params->get('displayedtext');

		if (empty($text))
		{
			$text = Text::_('COM_PROOFREADER');
		}

		return array(
			array(
				'link'   => 'index.php?option=com_proofreader&view=typos',
				'image'  => 'icon-pencil-2',
				'text'   => $text,
				'access' => array('core.manage', 'com_proofreader'),
				'id'     => 'plg_quickicon_proofreader'
			)
		);
	}
}
