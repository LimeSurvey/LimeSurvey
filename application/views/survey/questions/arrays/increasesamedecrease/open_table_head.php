<?php
/**
 * @var $show_no_answer
 */
?>
<!-- open_table_head -->
</colgroup>
<thead>
    <tr class="dontread">
        <th>&nbsp;</th>
        <th  class='th-5'><?php eT('Increase');?></th>
        <th class='th-6'><?php eT('Same');?></th>
        <th class='th-7'><?php eT('Decrease');?></th>
        <?php if($show_no_answer):?>
            <th class='th-8'><?php eT('No answer');?></th>
        <?php endif;?>
    </tr>
</thead>
<tbody>
