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

namespace Joomla\Plugin\System\Proofreader\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Proofreader\Site\Helper\FormHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for attaching Proofreader's CSS & JavaScript to the document
 *
 * @package     Proofreader
 * @subpackage  Plugins
 * @since       2.0
 */
final class Proofreader extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   5.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return array(
			'onAfterRender'   => 'onAfterRender',
			'onAfterDispatch' => 'onAfterDispatch'
		);
	}

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  $dispatcher  The object to observe
	 * @param   array                $config      An optional associative array of configuration settings.
	 *                                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                                            (this list is not meant to be comprehensive).
	 *
	 * @throws  \Exception
	 * @since   1.5
	 */
	public function __construct(DispatcherInterface $dispatcher, array $config = array())
	{
		parent::__construct($dispatcher, $config);

		$this->app = $this->getApplication() ?: $this->app;

		// Use this plugin only in site application.
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$this->params = ComponentHelper::getParams('com_proofreader');
	}

	/**
	 * This method embeds Proofreader's form into the document's body
	 *
	 * @param   Event  $event  The CMS event we are handling.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function onAfterRender(Event $event)
	{
		$document = $this->app->getDocument();
		$print    = (int) $this->app->input->get('print', 0);
		$offline  = (int) $this->app->get('offline', 0);

		if ($this->app->getName() == 'site' && $document->getType() == 'html' && $print === 0 && ($offline === 0))
		{
			$buffer = $this->app->getBody();
			$form   = $this->app->getUserState('com_proofreader.typo.form');

			if (!empty($buffer) && !empty($form))
			{
				if ($this->params->get('highlight', 1) == 1)
				{
					$user = $this->app->getIdentity();

					if ($user->authorise('core.admin') || $user->authorise('core.manage', 'com_proofreader'))
					{
						$buffer = preg_replace(
							'#(<body[^\>]*?>)#ism',
							'\\1<div class="proofreader_highlight">',
							$buffer
						);
						$buffer = str_replace('</body>', '</div></body>', $buffer);
					}
				}

				$buffer = str_replace('</body>', $form . '</body>', $buffer);
				$this->app->setBody($buffer);
			}
		}
	}

	/**
	 * This method attaches stylesheet and javascript files to the document
	 *
	 * @return  void
	 * @since   2.0
	 */
	public function onAfterDispatch()
	{
		$document = $this->app->getDocument();
		$print    = $this->app->input->getInt('print', 0);
		$offline  = (int) $this->app->get('offline', 0);

		if ($document->getType() == 'html')
		{
			if ($print === 0 && ($offline === 0))
			{
				/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
				$wa = $document->getWebAssetManager();

				/** @var \Joomla\CMS\WebAsset\WebAssetRegistry $wr */
				$wr = $wa->getRegistry();
				$wr->addRegistryFile('media/com_proofreader/joomla.asset.json');

				if ($this->params->get('disable_css', 0) == 0)
				{
					$style = $this->app->getLanguage()->isRTL() ? 'proofreader.style.rtl' : 'proofreader.style';
					$wa->useStyle($style);
				}

				$wa->useScript('proofreader.core');

				// Initialises the Proofreader's form and stores it into static variable
				$this->app->setUserState('com_proofreader.typo.form', FormHelper::getScript($this->params));

				if ($this->params->get('highlight', 1) == 1)
				{
					$user = $this->app->getIdentity();

					if ($user->authorise('core.admin') || $user->authorise('core.manage', 'com_proofreader'))
					{
						$this->initHighlighter();
					}
				}
			}
		}
	}

	/**
	 * This method initialises the Joomla's Highlighter
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function initHighlighter()
	{
		/** @var \Joomla\Database\DatabaseDriver $db */
		$db  = Factory::getContainer()->get('DatabaseDriver');
		$url = Uri::getInstance()->toString();

		$query = $db->getQuery(true)
			->select($db->quoteName('typo_text'))
			->from($db->quoteName('#__proofreader_typos'))
			->where($db->quoteName('page_url') . ' = ' . $db->quote($url));

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows))
		{
			$cleanTerms = array();

			foreach ($rows as $row)
			{
				$cleanTerms[] = $row->typo_text;
			}

			Factory::getApplication()->getDocument()
				->addScriptOptions(
					'highlight',
					[[
						'class'     => 'proofreader_highlight',
						'highLight' => $cleanTerms
					]]
				);
		}
	}
}
