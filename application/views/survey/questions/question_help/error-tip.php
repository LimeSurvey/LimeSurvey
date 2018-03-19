<?php
/**
 * Question error tips. For now, called from em_manager_helper::_ValidateQuestion and used for real error (db issue for example)
 * @var $qid
 * @var coreId : need to be the id of the tag (part of the API)
 * @var coreClass : need to be in class (part of the API)
 * @var $vclass
 * @var $vtip
 */
?>
<div id='<?php echo $coreId; ?>' class='ls-question-message alert alert-danger alert-dismissible <?php echo $coreClass; ?>'>
    <button type="button" class="close" data-dismiss="alert" aria-label="<?php echo gT("Close") ?>"><span aria-hidden="true">&times;</span></button>
    <span class='fa fa-exclamation' aria-hidden="true"></span>
    <?php echo $vtip; ?>
</div>
