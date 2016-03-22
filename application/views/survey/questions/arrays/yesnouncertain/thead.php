<?php
/**
 * @var $no_answer
 */
?>
<!-- thead -->
</colgroup>
<thead>
    <tr class="array1">
        <th>&nbsp;</th>
        <th class="dontread"><?php eT('Yes');?></th>
        <th class="dontread"><?php eT('Uncertain');?></th>
        <th class="dontread"><?php eT('No');?></th>
        <?php if($no_answer):?>
            <th class="dontread"><?php eT('No answer'); ?></th>
        <?php endif;?>
    </tr>
</thead>
<tbody>
    <?php if($anscount==0):?>
        <tr>
            <th class="answertext">
                <?php eT('Error: This question has no answers.');?>
            </th>
        </tr>
    <?php endif; ?>
