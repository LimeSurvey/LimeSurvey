<div class="header"><?php echo $clang->gT("Add Answer");?>: <?php echo $clang->gT("Question Selection");?></div><br />
<div class="messagebox">
	<?php echo $clang->gT("Sorry there are no supported question types in this survey.");?>
	<br/><br/><input type="submit" onclick="window.open('<?php echo site_url("admin/quotas/$iSurveyId");?>', '_top')" value="<?php echo $clang->gT("Continue");?>"/>
</div>