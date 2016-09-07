<?php
/**
 * Question tips. For now, called from em_manager_helper::_ValidateQuestion
 * @var $qid
 * @var $vclass
 * @var $vtip
 */
?>
<!-- views/survey/system/tips -->
<div id='vmsg_<?php echo $qid; ?>_<?php echo $vclass; ?>' class='em_<?php echo $vclass; ?> emtip '>

    <span class="icon icon-tip" aria-hidden="true"></span>
    <?php echo $vtip; ?>
</div>
