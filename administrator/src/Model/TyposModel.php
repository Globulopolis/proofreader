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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Model class for handling lists of typos.
 *
 * @since  3.0
 */
class TyposModel extends ListModel
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_proofreader.typos';

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'p.id',
				'author_id', 'p.created_by',
				'language', 'p.page_language',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to get an object implementing QueryInterface for retrieving the data set from a database.
	 *
	 * @return  \Joomla\Database\QueryInterface  An object implementing QueryInterface to retrieve the data set.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				array(
					$db->quoteName('p.id'),
					$db->quoteName('p.typo_text'),
					$db->quoteName('p.typo_prefix'),
					$db->quoteName('p.typo_suffix'),
					$db->quoteName('p.typo_comment'),
					$db->quoteName('p.page_url'),
					$db->quoteName('p.page_title'),
					$db->quoteName('p.page_language'),
					$db->quoteName('p.created'),
					$db->quoteName('p.created_by'),
					$db->quoteName('p.created_by_ip'),
					$db->quoteName('p.created_by_name')
				)
			)
		);
		$query->from($db->quoteName('#__proofreader_typos', 'p'));

		// Join over the users
		$query->select($db->quoteName('u.name', 'author'))
			->leftJoin($db->quoteName('#__users', 'u'), 'u.id = p.created_by');

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->leftJoin($db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('p.page_language'));

		// Filter by user
		$authors = $this->getState('filter.author_id');
		$authors = ArrayHelper::toInteger($authors);

		if (!empty($authors))
		{
			$query->where('p.created_by IN (' . implode(',', $authors) . ')');
		}

		// Filter by language
		$language = $this->getState('filter.language');

		if ($language != '')
		{
			$query->where('p.page_language = ' . $db->Quote($db->escape($language)));
		}

		// Filter by search in name or email
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('p.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('p.typo_text LIKE ' . $search);
			}
		}

		$ordering  = $this->state->get('list.ordering', 'p.created');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($db->escape($ordering . ' ' . $direction));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'p.created', $direction = 'asc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$language = $app->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}
}
