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

extract($displayData);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type"/>
	<meta name="Generator" content="Proofreader"/>
</head>
<body>
<div style="margin: 0 0 10px 0;">
	<?php echo Text::_('COM_PROOFREADER_NOTIFICATION_MESSAGE_PAGE'); ?>:
</div>
<div style="margin: 10px 0;">
	<a href="<?php echo $page_url; ?>" target="_blank"><?php echo $page_title; ?></a>
</div>
<div style="margin: 10px 0;">
	<?php echo Text::_('COM_PROOFREADER_NOTIFICATION_MESSAGE_TYPO'); ?>:
</div>
<div style="border: 1px solid #ccc; padding: 10px 5px; margin: 5px 0;">
	<?php if (!empty($typo_prefix)): ?>
		<?php echo $typo_prefix; ?>
	<?php endif; ?>
	<span style="color: #f00;"><?php echo $typo_text; ?></span>
	<?php if (!empty($typo_suffix)): ?>
		<?php echo $typo_suffix; ?>
	<?php endif; ?>
</div>
<?php if (!empty($typo_comment)): ?>
	<div style="margin: 10px 0;">
		<?php echo Text::_('COM_PROOFREADER_NOTIFICATION_MESSAGE_COMMENT'); ?>:
	</div>
	<div style="border: 1px solid #ccc; padding: 10px 5px; margin: 5px 0;">
		<?php echo $typo_comment; ?>
	</div>
<?php endif; ?>
</body>
</html>
