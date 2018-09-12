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
<tr class="array1 header_row dontread <?php echo $class; ?>">

    <th class="header_answer_text">&nbsp;</th>

    <?php foreach ($labelans0 as $ld): ?>
        <th class='th-12 text-center'><?php echo $ld; ?></th>
    <?php endforeach; ?>

    <?php if (count($labelans1) > 0): ?>

        <td class="header_separator">&nbsp;</td>  <!-- Separator : and No answer for accessibility for first colgroup -->

        <?php foreach ($labelans1 as $ld): ?>
            <th  class='th-13 text-center'><?php echo $ld; ?></th>
        <?php endforeach; ?>

    <?php endif; ?>

    <?php if ($shownoanswer || $rightexists): ?>
        <td class="header_separator rigth_separator">&nbsp;</td>
    <?php endif; ?>

    <?php if ($shownoanswer): ?>
        <th class="header_no_answer text-center"><?php eT('No answer'); ?></th>
    <?php endif; ?>

</tr>
