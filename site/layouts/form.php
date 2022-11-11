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
?>
<form id="proofreader_form" action="<?php echo $displayData['action']; ?>" class="proofreader_form" method="post">
    <h2 class="proofreader_form_header"><?php echo JText::_('COM_PROOFREADER_HEADER'); ?></h2>

    <div id="proofreader_messages_container"></div>
    <div class="proofreader_form_body">
        <div><?php echo JText::_('COM_PROOFREADER_FIELD_TYPO_LABEL'); ?></div>
        <div id="proofreader_typo_container" class="proofreader_typo_container"></div>

        <?php foreach ($displayData['form']->getFieldset('basic') as $field) : ?>
            <div class="control-group">
                <div class="control-label">
                    <?php echo $field->label; ?>
                </div>
                <div class="controls">
                    <?php echo $field->input; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="control-group">
            <div class="controls">
                <button type="submit" id="proofreader_submit" class="btn btn-primary">
                    <i class="icon-ok"></i>
                    <?php echo JText::_('JSUBMIT'); ?>
                </button>
            </div>
        </div>
        <div>
            <?php foreach ($displayData['form']->getFieldset('hidden') as $field) : ?>
                <?php echo $field->input; ?>
            <?php endforeach; ?>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
