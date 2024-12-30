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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var array $displayData */

echo $displayData['event']->proofreaderFormBeforeDisplay;

/** @note Use 'controller' var in form action URL because task=typo.save will throw 404 error when SEF is on. */
?>
<form action="<?php echo Route::_('index.php?option=com_proofreader&controller=typo&task=save'); ?>" method="post"
	  name="proofreaderForm" id="proofreaderForm" class="form-validate">
	<?php
	$proofreaderModalData = [
		'selector' => 'proofreaderModal',
		'params'   => [
			'modalCss' => 'modal-dialog-centered modal-lg',
			'title'    => Text::_('COM_PROOFREADER_HEADER'),
			'footer'   => LayoutHelper::render('form-modal-footer', null, JPATH_SITE . '/components/com_proofreader/layouts')
		],
		'body' => LayoutHelper::render('form-modal-body', $displayData, JPATH_SITE . '/components/com_proofreader/layouts')
	];

	echo LayoutHelper::render('libraries.html.bootstrap.modal.main', $proofreaderModalData);
	?>
</form>
<?php echo $displayData['event']->proofreaderFormAfterDisplay;
