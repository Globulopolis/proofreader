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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 * @see Joomla\Component\Proofreader\Site\Helper\FormHelper::getForm()
 */
/** @var Joomla\CMS\Form\FormField $form */

extract($displayData);
?>
<div id="proofreader_messages_container"></div>
<div class="main-card">
	<div><?php echo Text::_('COM_PROOFREADER_FIELD_TYPO_LABEL'); ?></div>
	<div id="proofreader_typo_container" class="border rounded p-1"></div>

	<?php echo $event->proofreaderFormPrepend; ?>

	<?php foreach ($form->getFieldset('basic') as $field) : ?>
		<?php if ($field->name != 'captcha'): ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php echo $field->input; ?>
			</div>
		</div>
		<?php else: ?>
			<?php if ($captchaEnabled): ?>
				<?php echo $field->renderField('captcha'); ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php foreach ($form->getFieldset('hidden') as $field):
		echo $field->renderField();
	endforeach;

	echo $event->proofreaderFormAppend;

	echo HTMLHelper::_('form.token'); ?>
</div>
