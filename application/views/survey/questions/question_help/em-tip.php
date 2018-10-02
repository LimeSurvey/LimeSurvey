<?php
/**
 * Question tips. For now, called from em_manager_helper::_ValidateQuestion
 * @var $qid
 * @var coreId : need to be the id of the tag (part of the API)
 * @var coreClass : need to be in class (part of the API)
 * @var $vclass
 * @var $vtip
 */
?>
<?php if (isset($hideTip) && $hideTip === false){ ?>
<div id='<?php echo $coreId; ?>' class='ls-question-message <?php echo $coreClass; ?>'>
    <span class='fa fa-exclamation-circle' aria-hidden="true"></span>
    <?php echo $vtip; ?>
</div>
<?php } ?>