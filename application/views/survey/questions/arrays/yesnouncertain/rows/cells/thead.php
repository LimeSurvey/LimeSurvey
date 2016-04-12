<?php
/**
 * @var $no_answer
 */
?>

<!-- thead -->
<th class="dontread text-center"><?php eT('Yes');?></th>
<th class="dontread text-center"><?php eT('Uncertain');?></th>
<th class="dontread text-center"><?php eT('No');?></th>
<?php if($no_answer):?>
    <th class="dontread text-center"><?php eT('No answer'); ?></th>
<?php endif;?>
<!-- end of  thead -->
