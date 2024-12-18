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

namespace Joomla\Component\Proofreader\Administrator\View\About;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Filesystem\Path;

/**
 * Base HTML View class for 'About' view
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Component data from XML file
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $component;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   4.0
	 */
	public function display($tpl = null)
	{
		$toolbar = Toolbar::getInstance();
		$user    = Factory::getApplication()->getIdentity();
		$this->component = Installer::parseXMLInstallFile(Path::clean(JPATH_ROOT . '/administrator/components/com_proofreader/proofreader.xml'));

		ToolbarHelper::title(Text::_('COM_PROOFREADER_MANAGER_ABOUT'));

		if ($user->authorise('core.admin', 'com_proofreader'))
		{
			$toolbar->preferences('com_proofreader');
		}

		parent::display($tpl);
	}
}
