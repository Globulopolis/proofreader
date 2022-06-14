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

class ProofreaderViewTypos extends JViewLegacy
{
	protected $bootstrap;
	protected $filter;
	protected $sidebar;
	protected $items;
	protected $pagination;
	protected $state;
	protected $authors;

	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			ProofreaderHelper::addSubmenu('typos');
		}

		$this->items      = $this->get('Items');
		$this->state      = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->authors    = $this->get('Authors');

        if (version_compare(JVERSION, '4.0', 'ge'))
        {
            $this->filterForm = $this->get('FilterForm');
        }

		if (version_compare(JVERSION, '3.0', 'ge') && version_compare(JVERSION, '4.0', 'le'))
		{
			JHtml::_('bootstrap.tooltip');
			JHtml::_('formbehavior.chosen', 'select');

			JHtmlSidebar::setAction('index.php?option=com_proofreader&view=typos');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_AUTHOR'),
				'filter_author_id',
				JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'))
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_LANGUAGE'),
				'filter_language',
				JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'))
			);

			$this->bootstrap = true;
			$this->sidebar   = JHtmlSidebar::render();
		}
		else
		{
			$filter       = JHTML::_('select.genericlist', $this->authors, 'filter_author_id', 'onchange="Joomla.submitform();"', 'value', 'text', $this->state->get('filter.author_id'));
			$this->filter = &$filter;
		}

		$this->addToolbar();

		parent::display($tpl);

	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_PROOFREADER_MANAGER_TYPOS'), 'pencil proofreader');

		$canDo = ProofreaderHelper::getActions();

		if (($canDo->get('core.delete')))
		{
			JToolBarHelper::deletelist('', 'typos.delete');
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_proofreader', '600', '800');
		}
	}

	protected function getSortFields()
	{
		return array(
			'p.typo_text'     => JText::_('COM_PROOFREADER_HEADING_TYPO'),
			'p.created'       => JText::_('JDATE'),
			'p.created_by'    => JText::_('JAUTHOR'),
			'p.page_language' => JText::_('JGRID_HEADING_LANGUAGE'),
			'p.id'            => JText::_('JGRID_HEADING_ID')
		);
	}
}
