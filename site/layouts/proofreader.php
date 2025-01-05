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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * @var array $displayData
 * @see Joomla\Component\Proofreader\Site\Helper\FormHelper::getForm()
 */

Factory::getApplication()->getDocument()->getWebAssetManager()
	->useScript('form.validate');

HTMLHelper::_('bootstrap.modal');
HTMLHelper::_('bootstrap.alert');
HTMLHelper::_('bootstrap.toast', '#proofreaderToast');
?>
<div id="proofreader_container" style="display: none;"><?php echo $displayData['form']; ?></div>
<div aria-live="polite" aria-atomic="true" class="d-flex position-absolute w-100 top-50">
	<div class="toast position-relative top-50 start-50 translate-middle" id="proofreaderToast" role="alert"
		 aria-live="assertive" aria-atomic="true">
		<div class="d-flex">
			<div class="toast-body"><?php echo Text::_('ERROR'); ?></div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
					aria-label="<?php echo Text::_('JCLOSE'); ?>"></button>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function ($) {
		$('#proofreader_container').proofreader({
			'handlerType'        : '<?php echo $displayData['options']['handler']; ?>',
			<?php if (isset($displayData['options']['load_form_url'])): ?>
			'loadFormUrl'        : '<?php echo $displayData['options']['load_form_url']; ?>',
			<?php endif; ?>
			<?php if ($displayData['options']['highlight']): ?>
			'highlightTypos'     : true,
			'highlightClass'     : 'mark',
			<?php endif; ?>
			'selectionMaxLength' : <?php echo $displayData['options']['selection_limit']; ?>,
			'floatingButtonDelay': 4000
		},
		{
			'reportTypo'           : Joomla.Text._('COM_PROOFREADER_BUTTON_REPORT_TYPO'),
			'thankYou'             : Joomla.Text._('COM_PROOFREADER_MESSAGE_THANK_YOU'),
			'browserIsNotSupported': Joomla.Text._('COM_PROOFREADER_ERROR_BROWSER_IS_NOT_SUPPORTED'),
			'selectionIsTooLarge'  : Joomla.Text._('COM_PROOFREADER_ERROR_TOO_LARGE_TEXT_BLOCK')
		});
	});
</script>
