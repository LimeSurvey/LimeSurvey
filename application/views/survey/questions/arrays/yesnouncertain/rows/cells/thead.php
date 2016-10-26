<?php
/**
 * @var $no_answer
 */
?>

<!-- thead -->
<th class="dontread"><?php eT('Yes');?></th>
<th class="dontread"><?php eT('Uncertain');?></th>
<th class="dontread"><?php eT('No');?></th>
<?php if($no_answer):?>
    <th class="dontread"><?php eT('No answer'); ?></th>
<?php endif;?>
<!-- end of  thead -->
