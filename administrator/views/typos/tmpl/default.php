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

$listOrder      = $this->escape($this->state->get('list.ordering'));
$listDirection  = $this->escape($this->state->get('list.direction'));
$containerClass = empty($this->sidebar) ? '' : 'span10';
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		var table = document.getElementById("sortTable"),
			direction = document.getElementById("directionTable"),
			order = table.options[table.selectedIndex].value,
			dir = 'asc';
		if (order == '<?php echo $listOrder; ?>') {
			dir = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dir, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_proofreader&view=typos'); ?>" method="post" name="adminForm"
      id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php endif; ?>
	<div id="j-main-container" class="<?php echo $containerClass; ?>">
		<?php if (version_compare(JVERSION, '4.0', 'ge'))
        {
            //echo Joomla\CMS\Layout\LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        }
        else
        {
            echo $this->loadTemplate('filter');
        } ?>

		<table class="adminlist table table-striped" id="articleList" cellspacing="1">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="20%" class="left nowrap">
					<?php echo JHTML::_('grid.sort', 'COM_PROOFREADER_HEADING_TYPO', 'p.typo_text', $listDirection, $listOrder); ?>
				</th>
				<th width="30%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'COM_PROOFREADER_HEADING_COMMENT', 'p.typo_comment', $listDirection, $listOrder); ?>
				</th>
				<th width="20%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'COM_PROOFREADER_HEADING_URL', 'p.page_title', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'JAUTHOR', 'author', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="center nowrap hidden-phone">
					<?php echo JHTML::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'p.page_language', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="center nowrap hidden-phone">
					<?php echo JHTML::_('grid.sort', 'JDATE', 'p.created', $listDirection, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $listDirection, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHTML::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="nowrap has-context">
						<?php echo $this->escape($item->typo_text); ?>
					</td>
					<td class="left hidden-phone">
						<?php echo $this->escape($item->typo_comment); ?>
					</td>
					<td class="left hidden-phone">
						<a href="<?php echo $item->page_url; ?>"
						   target="_blank"><?php echo $this->escape($item->page_title); ?></a>
					</td>
					<td class="left hidden-phone">
						<?php if (empty($item->author)): ?>
							<?php echo JText::_('COM_PROOFREADER_GUEST'); ?>
						<?php else: ?>
							<?php echo $this->escape($item->author); ?>
						<?php endif; ?>
					</td>
					<td class="center nowrap hidden-phone">
						<?php echo $this->escape($item->page_language); ?>
					</td>
					<td class="center nowrap hidden-phone">
						<?php echo JHtml::_('date', $item->created, 'Y-m-d H:i'); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>
