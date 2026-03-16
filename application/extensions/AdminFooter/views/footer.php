<div class='footer'>
    <div style='float:left;width:110px;text-align:left;'>
        <a href='http://manual.limesurvey.org'><img alt='LimeSurvey - <?php eT("Online manual"); ?>' title='LimeSurvey - <?php eT("Online manual"); ?>' src='<?php echo Yii::app()->getConfig('adminimageurl'); ?>docs.png' /></a>
    </div>
    <div style='float:right;'>
        <a href='https://donate.limesurvey.org' target="_blank"><img alt='<?php printf(gT("Support this project - Donate to %s!"), "LimeSurvey"); ?>' title='<?php printf(gT("Support this project - Donate to %s!"), "LimeSurvey"); ?>' src='<?php echo Yii::app()->getConfig('adminimageurl'); ?>donate.png'/></a>
    </div>
    <div class='subtitle'><a class='subtitle' title='<?php eT("Visit our website!"); ?>' href='https://community.limesurvey.org' target='_blank'>LimeSurvey Community Edition</a><br /><?php echo $versiontitle . " " . $versionnumber . $buildtext; ?></div>
</div>
