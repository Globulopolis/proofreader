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

defined('_JEXEC') or die;

/**
 * Proofreader component helper.
 *
 * @package    Proofreader
 */
class ProofreaderHelper
{
	/**
	 * Configure the Submenu links.
	 *
	 * @param  string $vName The name of the active view.
	 *
	 * @return void
	 */
	public static function addSubmenu($vName)
	{
        if (version_compare(JVERSION, '4.0', 'ge'))
        {
            return;
        }
		elseif (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtmlSidebar::addEntry(
				JText::_('COM_PROOFREADER_SUBMENU_TYPOS'),
				'index.php?option=com_proofreader&view=typos',
				$vName == 'typos');
			JHtmlSidebar::addEntry(
				JText::_('COM_PROOFREADER_SUBMENU_ABOUT'),
				'index.php?option=com_proofreader&view=about',
				$vName == 'about'
			);
		}
		else
		{
			JSubMenuHelper::addEntry(
				JText::_('COM_PROOFREADER_SUBMENU_TYPOS'),
				'index.php?option=com_proofreader&view=typos',
				$vName == 'typos'
			);
			JSubMenuHelper::addEntry(
				JText::_('COM_PROOFREADER_SUBMENU_ABOUT'),
				'index.php?option=com_proofreader&view=about',
				$vName == 'about'
			);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return JObject
	 */
	public static function getActions()
	{
		$result  = new JObject;
		$actions = JAccess::getActionsFromFile(JPATH_COMPONENT_ADMINISTRATOR . '/access.xml');

		if ($actions !== false)
		{
			$user = JFactory::getUser();
			foreach ($actions as $action)
			{
				$result->set($action->name, $user->authorise($action->name, 'com_proofreader'));
			}
		}

		return $result;
	}

	public static function getVersion()
	{
		$result = array();
		$xml    = simplexml_load_file(JPATH_COMPONENT_ADMINISTRATOR . '/proofreader.xml', 'SimpleXMLElement');

		if ($xml !== false)
		{
			$version     = $xml->xpath('version');
			$createdDate = $xml->xpath('creationDate');

			$result['version'] = is_array($version) ? $version[0] : $version;
			$result['date']    = is_array($createdDate) ? $createdDate[0] : $createdDate;
		}

		return $result;
	}
}
