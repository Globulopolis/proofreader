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

class ProofreaderViewAbout extends JViewLegacy
{
	protected $bootstrap;
	protected $sidebar;
	protected $version;
	protected $donate;

	function display($tpl = null)
	{
		require_once(JPATH_SITE . '/components/com_proofreader/helpers/layout.php');
		$this->donate  = ProofreaderLayoutHelper::render('donate', array(), JPATH_ADMINISTRATOR . '/components/com_proofreader/layouts');
		$this->version = ProofreaderHelper::getVersion();

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		JHtml::_('proofreader.stylesheet');

        if (version_compare(JVERSION, '4.0', 'le'))
        {
            JHtml::_('behavior.framework');
            if (version_compare(JVERSION, '3.0', 'ge'))
            {
                ProofreaderHelper::addSubmenu('about');
                $this->bootstrap = true;
                $this->sidebar   = JHtmlSidebar::render();
            }
            else
            {
                ProofreaderHelper::addSubmenu('about');
            }
        }

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_PROOFREADER_MANAGER_ABOUT'), 'pencil proofreader');

		$canDo = ProofreaderHelper::getActions();

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_proofreader', '600', '800');
		}
	}
}
