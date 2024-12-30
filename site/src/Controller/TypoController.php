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

namespace Joomla\Component\Proofreader\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\Proofreader\Site\Helper\FormHelper;

/**
 * Typo JSON controller for Proofreader.
 *
 * @package  Proofreader
 * @since    2.0
 */
class TypoController extends FormController
{
	/**
	 * Method to get Proofreader's form.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function form()
	{
		$this->app->mimeType = 'application/json';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		if (!$this->checkToken('get', false))
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);

			$this->app->setHeader('Status', $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
			$this->app->sendHeaders();
			$this->app->close();
		}

		echo new JsonResponse($this->getFormResponse($this->app->input->getString('url'), $this->app->input->getString('title')));

		$this->app->close();
	}

	/**
	 * Method to save a typo.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->app->mimeType = 'application/json';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		if (!$this->checkToken('post', false))
		{
			echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);

			$this->app->setHeader('Status', $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
			$this->app->sendHeaders();
			$this->app->close();
		}

		/** @var \Joomla\Component\Proofreader\Site\Model\TypoModel $model */
		$model = $this->getModel();
		$data  = $this->input->post->get('proofreader', array(), 'array');
		$form  = $model->getForm($data, false);

		if (!$form)
		{
			echo new JsonResponse(null, $model->getError(), true);

			$this->app->close();
		}

		$objData = (object) $data;
		$this->getDispatcher()->dispatch(
			'onContentNormaliseRequestData',
			AbstractEvent::create(
				'onContentNormaliseRequestData',
				array($this->option . '.' . $this->context, $objData, $form, 'subject' => new \stdClass)
			)
		);
		$data = (array) $objData;

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			$errorsArr = array();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$errorsArr[] = $errors[$i]->getMessage();
				}
				else
				{
					$errorsArr[] = $errors[$i];
				}
			}

			echo new JsonResponse(null, implode('<br>', $errorsArr), true);

			$this->app->close();
		}

		if (!$model->save($validData))
		{
			echo new JsonResponse(null, Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), true);

			$this->app->close();
		}

		echo new JsonResponse($this->getFormResponse($validData['page_url'], $validData['page_title']));

		$this->app->close();
	}

	/**
	 * Returns an object with Proofreader's form code and used scripts
	 *
	 * @param   string  $url    The page link
	 * @param   string  $title  The page title
	 *
	 * @return  object
	 *
	 * @since   2.0
	 */
	private function getFormResponse($url, $title)
	{
		$response       = new \StdClass;
		$response->form = FormHelper::getForm($url, $title);
		$params         = ComponentHelper::getParams('com_proofreader');

		if ($params->get('captcha') !== '' && $params->get('captcha') !== '0' && $params->get('dynamic_form_load', 0) == 1)
		{
			$scripts           = FormHelper::getFormScripts();
			$response->scripts = $scripts['scripts'];
			$response->script  = $scripts['script'];
		}
		else
		{
			$response->scripts = array();
			$response->script = '';
		}

		return $response;
	}
}
