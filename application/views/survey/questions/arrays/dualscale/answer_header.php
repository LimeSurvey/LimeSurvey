<?php

/**
 * Header row for dual-scale array
 * @var $labelans0 - Labels for answers
 * @var $labelans1
 * @var $shownoanswer
 * @var $rightexists
 * @var $class - Extra class, like 'repeat headers'
 */

?>
<tr class="ls-heading <?php echo $class; ?>" aria-hidden="true">
    <td></td>
    <?php if ($leftheader != '' || $rightheader !=''): ?>
        <td class="hidden"></td>
    <?php endif; ?>
    <?php foreach ($labelans0 as $ld): ?>
        <th class='answer-text'><?php echo $ld; ?></th>
    <?php endforeach; ?>
    <?php if (count($labelans1) > 0): ?>
        <td class="header_separator"><?php if ($shownoanswer): ?>
        <div class="ls-js-hidden"><?php eT('No answer'); ?></div>
        <?php endif; ?></td>  <!-- Separator : and No answer for accessibility for first colgroup -->
        <?php if ($leftheader != '' || $rightheader !=''): ?>
            <td class="hidden"></td>
        <?php endif; ?>
        <?php foreach ($labelans1 as $ld): ?>
            <th class='answer-text'><?php echo $ld; ?></th>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($shownoanswer || $rightexists): ?>
        <td class="header_separator rigth_separator"></td>
    <?php endif; ?>
    <?php if ($shownoanswer): ?>
        <th class="answer-text noanswer-text"><?php eT('No answer'); ?></th>
    <?php endif; ?>
</tr>
