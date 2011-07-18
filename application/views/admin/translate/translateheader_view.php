<form name='translatemenu' id='translatemenu' action='<?php echo site_url("admin/translate/$surveyid/$tolang");?>' method='get' >
			  <?php echo translate::showTranslateAdminmenu($surveyid, $survey_title, $tolang); ?>
</form>

<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>

<div class='header ui-widget-header'><?php echo $clang->gT("Translate survey");?></div>
