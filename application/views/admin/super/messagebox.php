<?php $this->_getAdminHeader(Yii::app()->session['metaHeader']); ?>
<?php $this->_showadminmenu(); ?>
<div class='messagebox ui-corner-all'>
	<div class='<?php echo $class;?>'>
		<?php echo $title;?>
	</div>
	<?php echo $message;?>
</div>
<?php $this->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual")); ?>