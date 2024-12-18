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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;

class com_proofreaderInstallerScript
{
	protected static $minVersion = '4.4.0';

	public function preflight($type, $parent)
	{
		if (!version_compare(JVERSION, self::$minVersion, 'ge'))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_PROOFREADER_ERROR_UNSUPPORTED_JOOMLA_VERSION', self::$minVersion), 'error');

			return false;
		}

		return true;
	}

	public function postflight($type, $parent)
	{
		/** @var \Joomla\Database\DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');

		$src      = $parent->getParent()->getPath('source');
		$manifest = $parent->getParent()->manifest;
		$plugins  = $manifest->xpath('plugins/plugin');

		foreach ($plugins as $plugin)
		{
			$name  = (string) $plugin->attributes()->plugin;
			$group = (string) $plugin->attributes()->group;
			$path  = $src . '/plugins/' . $group;

			if (is_dir($src . '/plugins/' . $group . '/' . $name))
			{
				$path = $src . '/plugins/' . $group . '/' . $name;
			}

			$installer = new Installer;
			$installer->install($path);

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__extensions'));
			$query->set($db->quoteName('enabled') . ' = 1');
			$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
			$query->where($db->quoteName('element') . ' = ' . $db->quote($name));
			$query->where($db->quoteName('folder') . ' = ' . $db->quote($group));
			$db->setQuery($query);
			$db->execute();
		}

		$deprecatedFiles = array(JPATH_SITE . '/components/com_proofreader/controllers/typo.raw.php');

		foreach ($deprecatedFiles as $file)
		{
			if (is_file($file))
			{
				File::delete($file);
			}
		}
	}

	public function uninstall($parent)
	{
		/** @var \Joomla\Database\DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');

		$manifest = $parent->getParent()->manifest;
		$plugins  = $manifest->xpath('plugins/plugin');

		foreach ($plugins as $plugin)
		{
			$name  = (string) $plugin->attributes()->plugin;
			$group = (string) $plugin->attributes()->group;

			$query = $db->getQuery(true);
			$query->select($db->quoteName('extension_id'));
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('type') . ' = ' . $db->Quote('plugin'));
			$query->where($db->quoteName('element') . ' = ' . $db->Quote($name));
			$query->where($db->quoteName('folder') . ' = ' . $db->Quote($group));
			$db->setQuery($query);

			$extensions = $db->loadColumn();

			if (count($extensions))
			{
				foreach ($extensions as $id)
				{
					$installer = new Installer;
					$installer->uninstall('plugin', $id);
				}
			}
		}
	}
}
