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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');
jimport('joomla.error.error');

class com_proofreaderInstallerScript
{
    protected static $minimum_jversion = '2.5.14';

    function preflight($type, $parent)
    {
        if (!version_compare(JVERSION, self::$minimum_jversion, 'ge'))
        {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PROOFREADER_ERROR_UNSUPPORTED_JOOMLA_VERSION', self::$minimum_jversion), 'error');

            return false;
        }

        return true;
    }

    function postflight($type, $parent)
    {
        $db = JFactory::getDBO();

        $src      = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;
        $plugins  = $manifest->xpath('plugins/plugin');

        foreach ($plugins as $plugin)
        {
            $name  = (string) $plugin->attributes()->plugin;
            $group = (string) $plugin->attributes()->group;
            $path  = $src . '/plugins/' . $group;

            if (JFolder::exists($src . '/plugins/' . $group . '/' . $name))
            {
                $path = $src . '/plugins/' . $group . '/' . $name;
            }

            $installer = new JInstaller;
            $installer->install($path);

            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__extensions'));
            $query->set($db->quoteName('enabled') . ' = 1');
            $query->where($db->quoteName('type') . ' = ' . $db->Quote('plugin'));
            $query->where($db->quoteName('element') . ' = ' . $db->Quote($name));
            $query->where($db->quoteName('folder') . ' = ' . $db->Quote($group));
            $db->setQuery($query);
            $db->execute();
        }

        $deprecatedFiles = array(JPATH_SITE . '/components/com_proofreader/controllers/typo.raw.php');
        foreach ($deprecatedFiles as $file)
        {
            if (is_file($file))
            {
                JFile::delete($file);
            }
        }
    }

    function uninstall($parent)
    {
        $db = JFactory::getDBO();

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
                    $installer = new JInstaller;
                    $installer->uninstall('plugin', $id);
                }
            }
        }
    }
}
