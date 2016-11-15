<?php
/**
 * @var $no_answer
 */
?>

<!-- thead -->
<td></td>
<th class="answer-text"><?php eT('Yes');?></th>
<th class="answer-text"><?php eT('Uncertain');?></th>
<th class="answer-text"><?php eT('No');?></th>
<?php if($no_answer):?>
    <th class="answer-text noanswer-text"><?php eT('No answer'); ?></th>
<?php endif;?>
<!-- end of  thead -->
