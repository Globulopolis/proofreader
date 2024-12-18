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

if (is_dir(JPATH_SITE . '/language/ru-RU') || is_dir(JPATH_ADMINISTRATOR . '/language/ru-RU'))
{
	$homepageUrl = 'https://www.joomlatune.ru';
}
else
{
	$homepageUrl = "https://www.joomlatune.com";
}

preg_match('(\d+\.\d+)', $this->component['version'], $matches);
?>
<div class="main-card">
	<div class="row">
		<div class="m-2">
			<div class="mb-1">
				<span class="proofreader-name">Proofreader</span>
				<span class="proofreader-version"><?php echo $matches[0]; ?></span>
				<span class="proofreader-date">[<?php echo $this->component['creationDate']; ?>]</span>
			</div>
			<div class="my-2">
				<a href="https://github.com/PavelSyomin/proofreader/issues" target="_blank"
				   class="btn btn-success btn-sm"><?php echo Text::_('COM_PROOFREADER_ABOUT_SUPPORT'); ?></a>
			</div>
			<div class="mb-1">
				&copy; 2005-<?php echo date('Y'); ?> <a href="<?php echo $homepageUrl; ?>">JoomlaTune Team</a>. <?php echo Text::_('COM_PROOFREADER_ABOUT_COPYRIGHT'); ?>
			</div>
			<div class="mb-1">
				<?php echo Text::_('COM_PROOFREADER_XML_DESCRIPTION'); ?>
			</div>
			<div class="">
				<?php echo Text::_('COM_PROOFREADER_ABOUT_LICENSE'); ?>
			</div>
		</div>
	</div>
</div>
