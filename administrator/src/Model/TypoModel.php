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

namespace Joomla\Component\Proofreader\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class TypoModel extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $text_prefix = 'COM_PROOFREADER_TYPO';

	/**
	 * Method to get the typo's form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (count($data))
		{
			$this->setState('com_proofreader.typo.data', $data);
		}

		$form = $this->loadForm('com_proofreader.typo', 'typo', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.0
	 */
	protected function loadFormData()
	{
		$data = $this->getState('com_proofreader.typo.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
