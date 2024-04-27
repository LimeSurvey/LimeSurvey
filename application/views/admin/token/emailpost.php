<?php
/**
* Display email result
* @var $title string
* @var $message html
*/
if(!empty($nosidebodyblock)){ ?>
    <div class='side-body  <?php echo getSideBodyClass(false); ?>'>
    <!-- div not closed ? -->
<?php } ?>
<?php if (empty($lefttosend)) { // emailwarning loaded before
    $title = ($bEmail) ? gT("Sending invitations result") : gT("Sending reminders result");
    $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
        'title' => $title,
        'model' => $oSurvey,
    ));
} ?>
<div class="row" id="token-emailpost-messagebox">
    <div class="col-12 content-right">
        <div class="jumbotron message-box">
            <div style='border: 1px solid #ccc; max-height: 80em; overflow: scroll; text-align:left; padding-left:0.5em;'>
                <?php echo $tokenoutput ?>
            </div>
            <a href='<?= Yii::app()->getController()->createUrl("/admin/tokens/sa/browse/surveyid/{$surveyid}") ?>' class="btn btn-outline-secondary custom custom-margin top-10"><?= gT("Continue") ?></a>
        </div>
    </div>
</div>

