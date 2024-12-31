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

namespace Joomla\Component\Proofreader\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Helper to render a Layout object (supports Joomla 2.5)
 *
 * @package  Proofreader
 * @since    2.0
 */
class FormHelper
{
	protected static $loaded = array();

	/**
	 * Initialize helper. Load only once.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public static function initialize()
	{
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		$language = Factory::getApplication()->getLanguage();
		$language->load('com_proofreader', JPATH_ROOT, 'en-GB', true);
		$language->load('com_proofreader', JPATH_ROOT, null, true);

		self::$loaded[__METHOD__] = true;
	}

	/**
	 * Load main component layout
	 *
	 * @param   Registry  $params  Component parameters
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public static function getScript($params)
	{
		self::initialize();

		$dynamicFormLoad = $params->get('dynamic_form_load', 0);

		$displayData                               = array();
		$displayData['form']                       = $dynamicFormLoad ? '' : self::getForm();
		$displayData['options']                    = array();
		$displayData['options']['handler']         = $params->get('handler', 'keyboard');
		$displayData['options']['highlight']       = $params->get('highlight', 1) == 1;
		$displayData['options']['selection_limit'] = max($params->get('selection_limit', 100), 10);

		if ($dynamicFormLoad)
		{
			$displayData['options']['load_form_url'] = Route::_('index.php?option=com_proofreader&task=typo.form&' . Session::getFormToken() . '=1', false);
		}

		Text::script('COM_PROOFREADER_BUTTON_REPORT_TYPO', true);
		Text::script('COM_PROOFREADER_MESSAGE_THANK_YOU', true);
		Text::script('COM_PROOFREADER_ERROR_BROWSER_IS_NOT_SUPPORTED', true);
		Text::script('COM_PROOFREADER_ERROR_TOO_LARGE_TEXT_BLOCK', true);

		return LayoutHelper::render('proofreader', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');
	}


	/**
	 * 	Load form layout
	 *
	 * @param   string|null  $url    Page url
	 * @param   string|null  $title  Page title
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public static function getForm($url = null, $title = null)
	{
		self::initialize();

		$app = Factory::getApplication();

		/** @var \Joomla\Component\Proofreader\Site\Model\TypoModel $model */
		$model = $app->bootComponent('com_proofreader')->getMVCFactory()
			->createModel('Typo', 'Site');

		if (empty($url))
		{
			$url = Uri::getInstance()->toString();
		}

		if (empty($title))
		{
			$title = $app->getDocument()->getTitle();
		}

		$data               = array();
		$data['page_url']   = $url;
		$data['page_title'] = $title;
		$captchaSet         = ComponentHelper::getParams('com_proofreader')->get('captcha', $app->get('captcha', '0'));
		$captchaEnabled     = false;

		foreach (PluginHelper::getPlugin('captcha') as $plugin)
		{
			if ($captchaSet === $plugin->name)
			{
				$captchaEnabled = true;
				break;
			}
		}

		$dispatcher = $app->getDispatcher();
		$event      = new \StdClass;
		$subject    = new \StdClass;

		$eventResults = $dispatcher->dispatch(
			'onProofreaderFormBeforeDisplay',
			AbstractEvent::create(
				'onProofreaderFormBeforeDisplay',
				array(
					'eventClass' => 'Joomla\Component\Proofreader\Site\Event\FormEvent',
					'subject' => $subject, 'title' => $title, 'url' => $url
				)
			)
		)->getArgument('result', array());
		$event->proofreaderFormBeforeDisplay = trim(
			implode("\n", array_key_exists(0, $eventResults) ? $eventResults[0] : array())
		);

		$eventResults = $dispatcher->dispatch(
			'onProofreaderFormAfterDisplay',
			AbstractEvent::create(
				'onProofreaderFormAfterDisplay',
				array(
					'eventClass' => 'Joomla\Component\Proofreader\Site\Event\FormEvent',
					'subject' => $subject, 'title' => $title, 'url' => $url
				)
			)
		)->getArgument('result', array());
		$event->proofreaderFormAfterDisplay = trim(
			implode("\n", array_key_exists(0, $eventResults) ? $eventResults[0] : array())
		);

		$eventResults = $dispatcher->dispatch(
			'onProofreaderFormPrepend',
			AbstractEvent::create(
				'onProofreaderFormPrepend',
				array(
					'eventClass' => 'Joomla\Component\Proofreader\Site\Event\FormEvent',
					'subject' => $subject, 'title' => $title, 'url' => $url
				)
			)
		)->getArgument('result', array());
		$event->proofreaderFormPrepend = trim(
			implode("\n", array_key_exists(0, $eventResults) ? $eventResults[0] : array())
		);

		$eventResults = $dispatcher->dispatch(
			'onProofreaderFormAppend',
			AbstractEvent::create(
				'onProofreaderFormAppend',
				array(
					'eventClass' => 'Joomla\Component\Proofreader\Site\Event\FormEvent',
					'subject' => $subject, 'title' => $title, 'url' => $url
				)
			)
		)->getArgument('result', array());
		$event->proofreaderFormAppend = trim(
			implode("\n", array_key_exists(0, $eventResults) ? $eventResults[0] : array())
		);

		$displayData = array('form' => $model->getForm($data), 'captchaEnabled' => $captchaEnabled, 'event' => $event);

		return LayoutHelper::render('form', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');
	}

	/**
	 * Attach captcha scripts to json response.
	 *
	 * NOTE! Not all captcha scripts can work properly due to different implementations.
	 *
	 * @return   array
	 *
	 * @throws  \Exception
	 * @since   2.0
	 */
	public static function getFormScripts()
	{
		$app             = Factory::getApplication();

		/** @var \Joomla\CMS\Document\Document $document */
		$document        = $app->getDocument();
		$data            = array();
		$data['scripts'] = array();
		$data['script']  = '';

		// Some kind of magic to support CAPTCHA if dynamic form load is activated
		$headData    = $document->getHeadData();
		$scriptsDiff = array_keys($headData['scripts']);
		$scriptDiff  = array_values($headData['script']);

		$callbackSuffix = md5(UserHelper::genRandomPassword(16));
		$onloadCallback = 'onloadCallback_' . $callbackSuffix;

		if (count($scriptsDiff) === 0)
		{
			// Support for reCaptcha plugin https://github.com/nikosdion/plg_captcha_google
			if ($app->get('captcha') == 'google')
			{
				$scriptsDiff[] = $app->getDocument()->getWebAssetManager()->getAsset('script', 'plg_captcha_google.api')->getUri(false);
				$scriptDiff[] = 'grecaptcha';
			}
			elseif ($app->get('captcha') == 'hcaptcha')
			{
				$scriptsDiff[] = $app->getDocument()->getWebAssetManager()->getAsset('script', 'plg_captcha_hcaptcha.api')->getUri(false);
				$scriptDiff[] = 'hcaptcha';
			}
		}

		foreach ($scriptsDiff as $script)
		{
			if (StringHelper::strpos($script, 'recaptcha') !== false)
			{
				if (StringHelper::strpos($script, 'onloadCallback') !== false)
				{
					$data['scripts'][] = str_replace('onloadCallback', $onloadCallback, $script);
				}
				elseif (StringHelper::strpos($script, 'api.js') !== false)
				{
					if ($app->get('captcha') != 'google')
					{
						$data['scripts'][] = $script . '&onload=' . $onloadCallback;
					}
					else
					{
						$data['scripts'][] = $script;
					}
				}
				else
				{
					$data['scripts'][] = $script;
				}
			}
			elseif (StringHelper::strpos($script, 'challenges.cloudflare') !== false)
			{
				if (StringHelper::strpos($script, 'onloadCallback') !== false)
				{
					$data['scripts'][] = str_replace('onloadCallback', $onloadCallback, $script);
				}
				elseif (StringHelper::strpos($script, 'api.js') !== false)
				{
					$data['scripts'][] = $script . '?onload=' . $onloadCallback;
				}
				else
				{
					$data['scripts'][] = $script;
				}

				$scriptDiff[] = 'turnstile';
			}
			elseif (StringHelper::strpos($script, 'hcaptcha.com') !== false)
			{
				$data['scripts'][] = $script;
				$scriptDiff[] = 'hcaptcha';
			}
		}

		foreach ($scriptDiff as $script)
		{
			$matches = array();

			if (stripos($script, 'recaptcha') !== false)
			{
				if (preg_match_all('/(Recaptcha\.create[^\;]+\;)/ism', $script, $matches))
				{
					$data['script'] .= $matches[1][0];
				}
				elseif (preg_match_all('/(grecaptcha.render[^;]+;)/ism', $script, $matches) && $app->get('captcha') != 'google')
				{
					$data['script'] .= 'var ' . $onloadCallback . ' = function() {' . $matches[1][0] . '};';
				}
				else
				{
					$data['script'] .= 'plg_captcha_google_init=()=>[].slice.call(document.getElementsByClassName("g-recaptcha")).forEach(a=>grecaptcha.execute(grecaptcha.render(a,a.dataset)));';
				}
			}
			elseif (stripos($script, 'turnstile') !== false)
			{
				$data['script'] .= "var " . $onloadCallback . " = function() {};";
			}
		}

		return $data;
	}
}
