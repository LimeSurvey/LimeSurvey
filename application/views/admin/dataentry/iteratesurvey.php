<div class='header ui-widget-header'><?php $clang->eT("Iterate survey");?></div>

<?php if($success) {?>
<p style='width:100%;'>
<font class='successtitle'><?php $clang->eT("Success");?></font><br />
<?php $clang->eT("Answers and tokens have been re-opened.");?><br />
<?php } else { ?>
<div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php $clang->eT("Important instructions");?></div>
<br/><?php $clang->eT("Click on the following button if you want to");?>:<br />
<ol>
<li><?php $clang->eT("Delete all incomplete answers that correspond to a token for which a completed answers is already recorded");?></li>
<li><?php $clang->eT("Reset the completed answers to the incomplete state");?></li>
<li><?php $clang->eT("Reset all your tokens to the 'not used' state");?></li>
</ol><br />
<?php echo CHtml::form(array("admin/dataentry/sa/iteratesurvey/unfinalizeanswers/true/surveyid/".$surveyid), 'post');?>
<input type='submit' onclick="return confirm('<?php $clang->eT("Are you really sure you want to *delete* some incomplete answers and reset the completed state of both answers and tokens?","js");?>')" value='<?php $clang->eT("Reset answers and token completed state");?>' />
</form>
</div>
<?php }?>