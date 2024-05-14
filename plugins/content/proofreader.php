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
 * Proofreader content plugin
 *
 * @package     Proofreader
 * @subpackage  Plugins
 */
class plgContentProofreader extends JPlugin
{
    protected static $tag = 'proofreader-prompt';

    /**
     * Constructor
     *
     * @param  object $subject The object to observe
     * @param  array  $config  An array that holds the plugin configuration
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $language = JFactory::getLanguage();
        $language->load('com_proofreader', JPATH_SITE, 'en-GB', true);
        $language->load('com_proofreader', JPATH_SITE, null, true);

        $this->params = JComponentHelper::getParams('com_proofreader', true);
    }

    /**
     * Replaces tag {proofreader-prompt} within content with Proofreader's prompt block
     *
     * @param  string  $context  The context of the content being passed to the plugin.
     * @param  object  &$article The article object. Note $article->text is also available
     * @param  mixed   &$params  The article params
     * @param  integer $page     The 'page' number
     *
     * @return mixed   true if there is an error. Void otherwise.
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        if ($context == 'com_finder.indexer')
        {
            return true;
        }

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
     * @param  string  $context  The context of the content being passed to the plugin
     * @param  object  &$article The article object
     * @param  object  &$params  The article params
     * @param  integer $page     The 'page' number
     *
     * @return mixed   html string containing prompt text if in full article's view else boolean false
     */
    public function onContentAfterDisplay($context, &$article, &$params, $page = 0)
    {
        if ($context == 'com_content.article' || $context == 'com_content.featured' || $context == 'com_content.category')
        {
            $view = JFactory::getApplication('site')->input->get('view');
            $data = $params->toArray();

            // display in articles and skip modules' content
            if ($this->params->get('prompt', 1) == 1 && $view == 'article' && !isset($data['moduleclass_sfx']))
            {
                return $this->getPromptHtml();
            }
        }

        return '';
    }

    private function getPromptHtml()
    {
        $html = '<div class="proofreader_prompt">' . JText::_('COM_PROOFREADER_PROMPT') . '</div>';

        return $html;
    }
}
