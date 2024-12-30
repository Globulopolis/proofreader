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

namespace Joomla\Component\Proofreader\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\User\User;
use Joomla\Utilities\IpHelper;
use PHPMailer\PHPMailer\Exception as phpmailerException;

/**
 * Typo model class for the Proofreader.
 *
 * @package  Proofreader
 * @since    2.0
 */
class TypoModel extends FormModel
{
	/**
	 * Returns a JTable object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate. [optional]
	 * @param   string  $prefix  A prefix for the table class name. [optional]
	 * @param   array   $config  Configuration array for model. [optional]
	 *
	 * @return  \Joomla\Component\Proofreader\Administrator\Table\TypoTable
	 *
	 * @since   2.0
	 */
	public function getTable($type = 'Typo', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the Proofreader form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean   A Form object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$this->setState('com_proofreader.typo.data', $data);

		// Required because we under the com_content context.
		Form::addFormPath(JPATH_ROOT . '/components/com_proofreader/forms');

		$form = $this->loadForm('com_proofreader.typo', 'typo', array('control' => 'proofreader', 'load_data' => $loadData), true);

		if (empty($form))
		{
			return false;
		}

		$params = ComponentHelper::getParams('com_proofreader');

		if ($params->get('comment', 0) != 1)
		{
			$form->removeField('typo_comment');
		}

		if ($params->get('captcha') == '0')
		{
			$form->removeField('captcha');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The data for the form.
	 *
	 * @since   2.0
	 */
	protected function loadFormData()
	{
		return $this->getState('com_proofreader.typo.data', array());
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   2.0
	 */
	public function save($data)
	{
		$app   = Factory::getApplication();
		$user  = $app->getIdentity();
		$table = $this->getTable();

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		$table->id              = 0;
		$table->page_language   = $app->getLanguage()->getTag();
		$table->created         = Factory::getDate()->toSql();
		$table->created_by      = $user->get('id');
		$table->created_by_ip   = IpHelper::getIp();
		$table->created_by_name = $user->get('name');

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

		$params = ComponentHelper::getParams('com_proofreader');

		if ($params->get('notifications'))
		{
			$userId = (int) $params->get('editor');

			if (!empty($userId))
			{
				$config  = $app->getConfig();
				$user    = new User($userId);
				$subject = Text::sprintf('COM_PROOFREADER_NOTIFICATION_SUBJECT', $config->get('sitename'));

				$displayData = array(
					'page_url'     => $table->page_url,
					'page_title'   => $table->page_title,
					'typo_text'    => $table->typo_text,
					'typo_prefix'  => $table->typo_prefix,
					'typo_suffix'  => $table->typo_suffix,
					'typo_comment' => $table->typo_comment
				);

				$body = LayoutHelper::render('notification', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');

				try
				{
					/** @var \Joomla\CMS\Mail\Mail $mailer */
					$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();

					$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')))
						->addRecipient($user->email)
						->setSubject($subject)
						->setBody($body)
						->isHtml();

					// 'Error in Mail API' isn't an error. See https://github.com/joomla/joomla-cms/issues/25703#issuecomment-515047963
					$result = $mailer->Send();

					if ($result !== true)
					{
						Log::add(Text::_('COM_PROOFREADER_ERROR_NOTIFICATION_SEND_MAIL_FAILED'), Log::WARNING, 'com_proofreader');
					}
				}
				catch (MailDisabledException | phpMailerException $e)
				{
					Log::add($e->getMessage(), Log::WARNING, 'com_proofreader');
				}
			}
		}

		return true;
	}
}
