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

namespace Joomla\Plugin\Content\Proofreader\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Proofreader content plugin
 *
 * @package     Proofreader
 * @subpackage  Plugins
 * @since       2.0
 */
final class Proofreader extends CMSPlugin implements SubscriberInterface
{
	protected static $tag = 'proofreader-prompt';

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
			'onContentAfterDisplay' => 'onContentAfterDisplay',
			'onContentPrepare'      => 'onContentPrepare'
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

		$language = $this->app->getLanguage();
		$language->load('com_proofreader', JPATH_SITE, 'en-GB', true);
		$language->load('com_proofreader', JPATH_SITE, null, true);

		$this->params = ComponentHelper::getParams('com_proofreader');
	}

	/**
	 * Replaces tag {proofreader-prompt} within content with Proofreader's prompt block
	 *
	 * @param   Event  $event  The event instance.
	 *
	 * @return  void|boolean   True if there is an error. Void otherwise.
	 *
	 * @since   2.0
	 */
	public function onContentPrepare(Event $event)
	{
		$arguments = $event->getArguments();

		if ($arguments['context'] == 'com_finder.indexer')
		{
			return true;
		}

		$article = $arguments['subject'];

		if (!isset($article->text) || strpos('{' . $article->text . '}', self::$tag) === false)
		{
			return true;
		}

		$pattern       = '/\{' . self::$tag . '\}/i';
		$replacement   = $this->params->get('prompt', 1) == 1 ? $this->getPromptHtml() : '';
		$article->text = preg_replace($pattern, $replacement, $article->text, 1);
	}

	/**
	 * Displays the Proofreader's prompt text after the article's text
	 *
	 * @param   Event  $event  The event instance.
	 *
	 * @return  string
	 *
	 * @since   2.0
	 */
	public function onContentAfterDisplay(Event $event)
	{
		$arguments = $event->getArguments();

		if ($arguments['context'] == 'com_content.article' || $arguments['context'] == 'com_content.featured' || $arguments['context'] == 'com_content.category')
		{
			$view = Factory::getApplication()->input->get('view');
			$data = $arguments['params'];

			// Display in articles and skip modules' content
			if ($this->params->get('prompt', 1) == 1 && $view == 'article' && !$data->get('moduleclass_sfx') !== null)
			{
				return $this->getPromptHtml();
			}
		}

		return '';
	}

	private function getPromptHtml()
	{
		return '<div class="my-4 ps-4 text-secondary proofreader_prompt">' . Text::_('COM_PROOFREADER_PROMPT') . '</div>';
	}
}
