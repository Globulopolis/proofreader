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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\Component\Proofreader\Administrator\View\Typos\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_proofreader&view=typos'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<?php if (empty($this->items)): ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="adminlist table">
						<caption class="visually-hidden">
							<?php echo Text::_('A_SUBMENU_TYPOS'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-20 d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_PROOFREADER_HEADING_TYPO', 'p.typo_text', $listDirection, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JAUTHOR', 'author', $listDirection, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'p.page_language', $listDirection, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'p.created', $listDirection, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'p.id', $listDirection, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item):
							$item->language = $item->page_language;
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
								</td>
								<th scope="row" class="has-context">
									<div class="break-word">
										<?php echo $this->escape($item->typo_text); ?>
									</div>
									<div class="small">
										<?php if (!empty($item->typo_comment)): ?>
										<strong><?php echo Text::_('COM_PROOFREADER_HEADING_COMMENT'); ?>:</strong> <?php echo $this->escape($item->typo_comment); ?>
										<?php endif; ?>

										<?php if (!empty($item->page_url)): ?>
										<div>
											<strong><?php echo Text::_('COM_PROOFREADER_HEADING_URL'); ?>:</strong>
											<a href="<?php echo $item->page_url; ?>"
											   title="<?php echo $this->escape($item->page_title); ?>"
											   target="_blank"><?php echo $this->escape($item->page_title); ?></a>
										</div>
										<?php endif; ?>
									</div>
								</th>
								<td class="small d-none d-md-table-cell">
									<?php if (empty($item->author)): ?>
										<?php echo JText::_('COM_PROOFREADER_GUEST'); ?>
									<?php else: ?>
										<?php echo $this->escape($item->author); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo JHtml::_('date', $item->created, 'Y-m-d H:i'); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
