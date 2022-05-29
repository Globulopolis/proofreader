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

if (version_compare(JVERSION, '3.0', 'lt'))
{
	jimport('joomla.application.component.modelform');
}

/**
 * Typo model class for the Proofreader.
 *
 * @package     Proofreader
 */
class ProofreaderModelTypo extends JModelForm
{
	/**
	 * Returns a JTable object, always creating it.
	 *
	 * @param  string $type   The table type to instantiate. [optional]
	 * @param  string $prefix A prefix for the table class name. [optional]
	 * @param  array  $config Configuration array for model. [optional]
	 *
	 * @return JTable  A database object
	 */
	public function getTable($type = 'Typo', $prefix = 'ProofreaderTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the Proofreader form.
	 *
	 * @param  array   $data     An optional array of data for the form to interrogate.
	 * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return JForm A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(JPATH_ROOT . '/components/com_proofreader/models/forms');
		JForm::addFieldPath(JPATH_ROOT . '/components/com_proofreader/models/fields');

		$this->setState('com_proofreader.typo.data', $data);

		$form = $this->loadForm('com_proofreader.form', 'typo', array('control' => 'proofreader', 'load_data' => true), $loadData);
		if (empty($form))
		{
			return false;
		}

		$params = JComponentHelper::getParams('com_proofreader', true);

		if ($params->get('comment', 0) != 1)
		{
			$form->removeField('typo_comment');
		}

		if ($params->get('captcha') == "" || $params->get('captcha') == "0")
		{
			$form->removeField('captcha');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		return $this->getState('com_proofreader.typo.data', array());
	}

	/**
	 * Method to save the form data.
	 *
	 * @param  array $data The form data.
	 *
	 * @return boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$table = $this->getTable();
		$user  = JFactory::getUser();

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		$table->id              = 0;
		$table->page_language   = JFactory::getLanguage()->getTag();
		$table->created         = JFactory::getDate()->toSql();
		$table->created_by      = $user->get('id');
		$table->created_by_ip   = JFactory::getApplication()->input->server->get('REMOTE_ADDR', '', 'string');
		$table->created_by_name = $user->get('name');

        if (!$table->typo_comment)
        {
            $table->typo_comment = '';
        }

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$params = JComponentHelper::getParams('com_proofreader', true);
		if ($params->get('notifications'))
		{
			$editorId = $params->get('editor');
			if (!empty($editorId))
			{
				$config  = JFactory::getConfig();
				$editor  = JFactory::getUser($editorId);
				$subject = JText::sprintf('COM_PROOFREADER_NOTIFICATION_SUBJECT', $config->get('sitename'));

				$displayData                 = array();
				$displayData['page_url']     = $table->page_url;
				$displayData['page_title']   = $table->page_title;
				$displayData['typo_text']    = $table->typo_text;
				$displayData['typo_prefix']  = $table->typo_prefix;
				$displayData['typo_suffix']  = $table->typo_suffix;
				$displayData['typo_comment'] = $table->typo_comment;

				require_once(JPATH_SITE . '/components/com_proofreader/helpers/layout.php');

				$body = ProofreaderLayoutHelper::render('notification', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');

				$return = JFactory::getMailer()->sendMail($config->get('mailfrom'), $config->get('fromname'), $editor->email, $subject, $body, true);
				if ($return !== true)
				{
					$this->setError(JText::_('COM_PROOFREADER_ERROR_NOTIFICATION_SEND_MAIL_FAILED'));

					return false;
				}
			}
		}

		return true;
	}
}
