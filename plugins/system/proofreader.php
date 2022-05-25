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

jimport('joomla.plugin.plugin');
jimport('joomla.environment.uri');

/**
 * System plugin for attaching Proofreader's CSS & JavaScript to the document
 *
 * @package     Proofreader
 * @subpackage  Plugins
 */
class plgSystemProofreader extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param  object $subject The object to observe
	 * @param  array  $config  An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->params = JComponentHelper::getParams('com_proofreader', true);
	}

	/**
	 * This method checks if jQuery has already included by some extension and remove the Proofreader's one (Joomla 2.5)
	 *
	 * @return boolean  True on success
	 */
	function onBeforeRender()
	{
		if (version_compare(JVERSION, '3.0', '<'))
		{
			$app      = JFactory::getApplication();
			$document = JFactory::getDocument();
			$print    = (int) $app->input->get('print', 0);
			$offline  = 0; //(int) $app->get('offline', 0);

			if ($app->getName() == 'site' && $document->getType() == 'html' && $print === 0 && $offline === 0)
			{
				$headData    = $document->getHeadData();
				$scripts     = array_keys($headData['scripts']);
				$jqueryCount = 0;

				foreach ($scripts as $script)
				{
					if (preg_match('/\/jquery(\.min)?\.js/', $script))
					{
						$jqueryCount++;
					}
				}

				if ($jqueryCount > 1)
				{
					for ($i = 0, $n = count($scripts); $i < $n; $i++)
					{
						if (preg_match('/(proofreader).*\/jquery(\.min)?\.js/', $scripts[$i]))
						{
							unset($scripts[$i]);
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * This method embeds Proofreader's form into the document's body
	 *
	 * @return boolean  True on success
	 */
	function onAfterRender()
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$print    = (int) $app->input->get('print', 0);
		$offline  = 0; //(int) $app->get('offline', 0);

		if ($app->getName() == 'site' && $document->getType() == 'html' && $print === 0 && $offline === 0)
		{
			$buffer = $app->getBody();
			$form   = $app->getUserState('com_proofreader.typo.form');

			if (!empty($buffer) && !empty($form))
			{
				if ($this->params->get('highlight', 1) == 1)
				{
					$user = JFactory::getUser();
					if ($user->authorise('core.admin') || $user->authorise('core.manage', 'com_proofreader'))
					{
						$buffer = preg_replace('#(<body[^\>]*?>)#ism', '\\1<div id="proofreader_highlighter_start" style="display:none"></div>', $buffer);
						$buffer = str_replace('</body>', '<div id="proofreader_highlighter_end" style="display:none"></div></body>', $buffer);
					}
				}

				$buffer = str_replace('</body>', $form . '</body>', $buffer);
				$app->setBody($buffer);
			}
		}

		return true;
	}

	/**
	 * This method attaches stylesheet and javascript files to the document
	 *
	 * @return boolean  True on success
	 */
	function onAfterDispatch()
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$print    = (int) $app->input->get('print', 0);
		$offline  = 0; //(int) $app->get('offline', 0);

		if ($document->getType() == 'html')
		{
			if ($app->getName() == 'site')
			{
				if ($print === 0 && $offline === 0)
				{
					if ($this->params->get('disable_css', 0) == 0)
					{
						$style = JFactory::getLanguage()->isRTL() ? 'style_rtl.min.css' : 'style.min.css';
						JHtml::stylesheet('com_proofreader/' . $style, $options = array('relative' => true));
					}

					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						JHtml::_('jquery.framework');
					}
					else
					{
						JHtml::_('script', 'com_proofreader/jquery.min.js', false, true, false);
					}

					JHtml::script('com_proofreader/jquery.proofreader.min.js', $options = array('relative' => true));

					$this->initForm();

					if ($this->params->get('highlight', 1) == 1)
					{
						$user = JFactory::getUser();
						if ($user->authorise('core.admin') || $user->authorise('core.manage', 'com_proofreader'))
						{
							$this->initHighlighter();
						}
					}
				}
			}
			else
			{
				$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_proofreader/assets/css/icon.css');
			}
		}
	}

	/**
	 * This method initialises the Proofreader's form and stores it into static variable
	 */
	protected function initForm()
	{
		require_once(JPATH_SITE . '/components/com_proofreader/helpers/form.php');
		$form = ProofreaderFormHelper::getScript($this->params);
		JFactory::getApplication()->setUserState('com_proofreader.typo.form', $form);
	}

	/**
	 * This method initialises the Joomla's Highlighter
	 */
	protected function initHighlighter()
	{
		$db  = JFactory::getDbo();
		$url = JURI::getInstance()->toString();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('typo_text'));
		$query->from($db->quoteName('#__proofreader_typos'));
		$query->where($db->quoteName('page_url') . ' = ' . $db->quote($url));

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			$filter     = JFilterInput::getInstance();
			$cleanTerms = array();

			foreach ($rows as $row)
			{
				$cleanTerms[] = $row->typo_text;
			}

			JHtml::_('behavior.highlighter', $cleanTerms, 'proofreader_highlighter_start', 'proofreader_highlighter_end', 'proofreader_highlight');
		}
	}
}
