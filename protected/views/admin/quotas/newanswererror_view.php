<div class="header"><?php eT("Add answer");?>: <?php eT("Question Selection");?></div><br />
<div class="messagebox">
	<?php eT("Sorry there are no supported question types in this survey.");?>
	<br/><br/><input type="submit" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$iSurveyId");?>', '_top')" value="<?php eT("Continue");?>"/>
</div>