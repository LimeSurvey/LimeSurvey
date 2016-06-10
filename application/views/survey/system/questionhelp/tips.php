<?php
/**
 * Question tips. For now, called from em_manager_helper::_ValidateQuestion
 * @var $qid
 * @var $vclass
 * @var $vtip
 */
?>
<div id='vmsg_<?php echo $qid; ?>_<?php echo $vclass; ?>' class='em_<?php echo $vclass; ?> emtip '>

    <span class='fa fa-exclamation-circle' aria-hidden="true"></span>
    <?php echo $vtip; ?>
</div>
