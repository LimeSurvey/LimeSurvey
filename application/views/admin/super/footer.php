    <div class="push"></div>
</div>
<div class='footer'>
    <div style='float:left;width:110px;text-align:left;'>
    <a href='http://docs.limesurvey.org'><img alt='LimeSurvey - <?php $clang->eT("Online Manual");?>' title='LimeSurvey - <?php $clang->eT("Online manual");?>' src='<?php echo Yii::app()->getConfig('adminimageurl');?>docs.png' /></a>
    </div>
    <div style='float:right;'>
    <a href='http://donate.limesurvey.org'><img alt='<?php $clang->eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php $clang->eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo Yii::app()->getConfig('adminimageurl');;?>donate.png'/></a>
    </div>
    <div class='subtitle'><a class='subtitle' title='<?php $clang->eT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle." ".$versionnumber." ".$buildtext;?></div>
</div>
<?php
    if(!empty($js_admin_includes))
    {
        foreach ($js_admin_includes as $jsinclude)
        {
            ?>
            <script type="text/javascript" src="<?php echo $jsinclude;?>"></script>
            <?php
        }
    }
    if(!empty($css_admin_includes)) {
        foreach ($css_admin_includes as $cssinclude)
        {
            ?>
            <link rel="stylesheet" type="text/css" media="all" href="<?php echo $cssinclude;?>" />
            <?php
        }
    }
?>
</body>
</html>