<div class="header"><?php $clang->eT("Add Answer");?>: <?php $clang->eT("Question Selection");?></div><br />
<div class="messagebox">
	<?php $clang->eT("Sorry there are no supported question types in this survey.");?>
	<br/><br/><input type="submit" onclick="window.open('<?php echo site_url("admin/quotas/index/surveyid/$iSurveyId");?>', '_top')" value="<?php $clang->eT("Continue");?>"/>
</div>