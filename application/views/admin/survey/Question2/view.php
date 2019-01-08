<?php $this->renderPartial("./survey/Question/question_subviews/_ajax_variables", $ajaxDatas); ?>
<?php $this->renderPartial("./survey/Question2/_jsVariables", $jsData); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="pagetitle h3"><?php eT('Question'); ?>:  <?php echo  $qrrow['title'];?> <small>(ID: <?php echo  $qid;?>)</small></div>
    <div class="row" id="advancedQuestionEditor">
        <maineditor></maineditor>
        <generalsettings></generalsettings>
        <advancedsettings></advancedsettings>
    </div>
</div>
<div class="loading"