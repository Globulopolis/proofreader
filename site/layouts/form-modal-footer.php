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

use Joomla\CMS\Language\Text;

/** @var array $displayData */
?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('JCLOSE'); ?></button>
<button type="submit" class="btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
