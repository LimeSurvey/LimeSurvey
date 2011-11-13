<?php $this->_getAdminHeader(); ?>
<p><?php echo $errormsg; ?><br />
<?php echo $maxattempts; ?>
<br /><a href='<?php echo current_url();?>'><?php echo $clang->gT("Continue");?></a><br />
<?php $this->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual")) ?>
