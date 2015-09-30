    <div class="push"></div>
</div>
<?php
if (empty($versiontitle)) {
	$sep = DIRECTORY_SEPARATOR;
	$incfile = (__DIR__) . "$sep..$sep..$sep..{$sep}config{$sep}version.php";
	require ($incfile);
	$versiontitle='Version';
	$versionnumber = $config['versionnumber'];
	$buildtext = (!empty($config['buildnumber'])) ? 'Build ' . $config['buildnumber'] : '';
}
?>
<div class='footer'>
    <div style='float:left;width:110px;text-align:left;'>
        <a href='http://manual.limesurvey.org' target="_blank"><img alt='LimeSurvey - <?php eT("Online Manual"); ?>' title='LimeSurvey - <?php eT("Online manual"); ?>' src='<?php echo Yii::app()->getConfig('adminimageurl'); ?>docs.png' /></a>
    </div>
    <div style='float:right;'>
        <a href='http://donate.limesurvey.org' target="_blank"><img alt='<?php eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo Yii::app()->getConfig('adminimageurl'); ?>donate.png'/></a>
    </div>
    <div class='subtitle'><a class='subtitle' title='<?php eT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle . " " . $versionnumber . " " . $buildtext; ?></div>
</div>
</body>
</html>