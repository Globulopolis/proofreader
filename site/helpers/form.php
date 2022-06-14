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

/**
 * Helper to render a JLayout object (supports Joomla 2.5)
 *
 * @package     Proofreader
 */
class ProofreaderFormHelper
{
	protected static $loaded = array();

	public static function initialize()
	{
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		$language = JFactory::getLanguage();
		$language->load('com_proofreader', JPATH_ROOT, 'en-GB', true);
		$language->load('com_proofreader', JPATH_ROOT, null, true);

		JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_proofreader/models', 'ProofreaderModel');
		require_once(JPATH_SITE . '/components/com_proofreader/helpers/layout.php');

		self::$loaded[__METHOD__] = true;

		return;
	}

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
			$displayData['options']['load_form_url'] = JRoute::_('index.php?option=com_proofreader&task=typo.form&' . JSession::getFormToken() . '=1');
		}

		$html = ProofreaderLayoutHelper::render('proofreader', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');
		$html = preg_replace('/([\t\r\n])/im', ' ', $html);
		$html = preg_replace('/(\s){2,}/im', '\\1', $html);
		$html = preg_replace('/(\)|\:)\s(\{|\')/im', '\\1\\2', $html);

		return $html;

	}

	public static function getForm($url = null, $title = null)
	{
		self::initialize();

		$model = JModelLegacy::getInstance('Typo', 'ProofreaderModel');

		if (empty($url))
		{
			$url = JURI::getInstance()->toString();
		}

		if (empty($title))
		{
			$title = JFactory::getDocument()->getTitle();
		}

		$data               = array();
		$data['page_url']   = $url;
		$data['page_title'] = $title;
		$data['hash']       = md5($url . JFactory::getConfig()->get('secret'));

		$displayData           = array();
		$displayData['action'] = JRoute::_('index.php?option=com_proofreader&task=typo.submit');
		$displayData['form']   = $model->getForm($data);

		$html = ProofreaderLayoutHelper::render('form', $displayData, JPATH_SITE . '/components/com_proofreader/layouts');

		return $html;
	}

	public static function getFormScripts()
	{
		$data            = array();
		$data['scripts'] = array();
		$data['script']  = '';

		// some kind of magic to support reCAPTCHA if dynamic form load is activated
		$headData    = JFactory::getDocument()->getHeadData();
		$scriptsDiff = array_keys($headData['scripts']);
		$scriptDiff  = array_values($headData['script']);

		$callbackSuffix = md5(JUserHelper::genRandomPassword(16));
		$onloadCallback = 'onloadCallback_' . $callbackSuffix;

		foreach ($scriptsDiff as $script)
		{
			if (JString::strpos($script, 'recaptcha') !== false)
			{
				if (JString::strpos($script, 'onloadCallback') !== false)
				{
					$data['scripts'][] = str_replace('onloadCallback', $onloadCallback, $script);
				}
				else if (JString::strpos($script, 'api.js') !== false)
				{
					$data['scripts'][] = $script . '&onload=' . $onloadCallback;
				}
				else
				{
					$data['scripts'][] = $script;
				}
			}
		}

		foreach ($scriptDiff as $script)
		{
			$matches = array();
			if (preg_match_all('/(Recaptcha\.create[^\;]+\;)/ism', $script, $matches))
			{
				$data['script'] .= $matches[1][0];
			}
			else if (preg_match_all('/(grecaptcha.render[^;]+;)/ism', $script, $matches))
			{
				$data['script'] .= 'var ' . $onloadCallback . ' = function() {' . $matches[1][0] . '};';
			}
		}

		return $data;
	}
}
