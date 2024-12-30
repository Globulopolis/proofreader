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

namespace Joomla\Component\Proofreader\Site\Event;

defined('_JEXEC') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;

/**
 * Event class for basic form events
 *
 * @since  4.1
 */
class FormEvent extends AbstractImmutableEvent implements ResultAwareInterface
{
	use ResultAware;
	use ResultTypeArrayAware;

	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   4.1
	 */
	public function __construct($name, array $arguments = [])
	{
		parent::__construct($name, $arguments);
	}

	/**
	 * Set abort parameter to true
	 *
	 * @param   string  $reason  The abort reason text
	 *
	 * @return  void
	 *
	 * @since   4.1
	 */
	public function setAbort(string $reason)
	{
		$this->arguments['abort'] = true;
		$this->arguments['abortReason'] = $reason;
	}
}
