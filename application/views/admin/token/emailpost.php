<?php
/**
* Display email result
* @var $title string
* @var $message html
*/

if(isset($nosidebodyblock) && $nosidebodyblock === true ){ ?>
    <div class='side-body'>
<?php } ?>

<div class="row" id="token-emailpost-messagebox">
    <div class="col-12 content-right">
        <div class="jumbotron message-box">
            <h2><?php if ($bEmail) eT("Sending invitations..."); else eT("Sending reminders...");?></h2>
            <div style='border: 1px solid #ccc; max-height: 80em; overflow: scroll; text-align:left; padding-left:0.5em;'>
                <?php echo $tokenoutput ?>
            </div>
            <a href='<?= Yii::app()->getController()->createUrl("/admin/tokens/sa/browse/surveyid/{$surveyid}") ?>' class="btn btn-outline-secondary custom custom-margin top-10"><?= gT("Continue") ?></a>
        </div>
    </div>
</div>

