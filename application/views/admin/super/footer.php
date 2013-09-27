    <div class="push"></div>
</div>
<div class='footer'>
    <div style='float:left;width:110px;text-align:left;'>
    <a href='http://manual.limesurvey.org'><img alt='LimeSurvey - <?php $clang->eT("Online Manual");?>' title='LimeSurvey - <?php $clang->eT("Online manual");?>' src='<?php echo Yii::app()->getConfig('adminimageurl');?>docs.png' /></a>
    </div>
    <div style='float:right;'>
    <a href='http://donate.limesurvey.org'><img alt='<?php $clang->eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php $clang->eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo Yii::app()->getConfig('adminimageurl');;?>donate.png'/></a>
    </div>
    <div class='subtitle'><a class='subtitle' title='<?php $clang->eT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle." ".$versionnumber." ".$buildtext;?></div>
</div>
</body>
</html>