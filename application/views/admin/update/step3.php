<div class="header ui-widget-header"><?php sprintf($clang->gT('ComfortUpdate step %s'),'3'); ?></div>
<div class="updater-background">
<h3><?php $clang->eT('Creating DB & file backup')?></h3>
<div class='messagebox ui-corner-all'>
<?php
    if (!isset( Yii::app()->session['updateinfo']))
    {
        $clang->eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';

        if ($updateinfo['error']==1)
        {
            $clang->eT('Your update key is invalid and was removed. ').'<br />';
        }
        else
            $clang->eT('On requesting the update information from limesurvey.org there has been an error:').'<br />';
    } // Not sure we can continue
    ?>
    <?php if($result=="success") { ?>
        <div class='successheader'><?php $clang->eT("Success"); ?></div>
    <?php }elseif ($result=="warning" ) { ?>
        <div class='warningheader'><?php $clang->eT("Warning"); ?></div>
    <?php }else{ ?>
        <div class='errorheader'><?php $clang->eT("Failed"); ?></div>
    <?php } ?>
    <div class="<?php echo $aFileBackup['class']; ?>title"> <?php $clang->eT('Creating file backup... '); ?></div>
    <p><?php echo $aFileBackup['text']; ?></p>

    <div class="<?php echo $aFileBackup['class']; ?>title"> <?php $clang->eT('Creating database backup...'); ?></div>
    <p class="<?php echo $aSQLBackup['class']; ?>"><?php echo $aSQLBackup['text']; ?></p>
    <p class="information"><?php $clang->eT('Please check any problems above and then proceed to the final step.'); ?>
    <?php echo "<p><a class='button' href='".Yii::app()->getController()->createUrl("admin/update/sa/step4/")."'>
     ".sprintf($clang->gT('Proceed to step %s'),'4') ."</a></p>";
     ?>
</div>
</div>
