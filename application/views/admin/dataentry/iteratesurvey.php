<div class="side-body <?php echo getSideBodyClass(true); ?>">
<h3><?php eT("Iterate survey");?></h3>

<?php if($success) {?>
<p style='width:100%;'>
<font class='successtitle'><?php eT("Success");?></font><br />
<?php eT("Answers and tokens have been re-opened.");?><br />
<?php } else { ?>
<div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php eT("Important instructions");?></div>
<br/><?php eT("Click on the following button if you want to");?>:<br />
<ol>
<li><?php eT("Delete all incomplete answers that correspond to a token for which a completed answers is already recorded");?></li>
<li><?php eT("Reset the completed answers to the incomplete state");?></li>
<li><?php eT("Reset all your tokens to the 'not used' state");?></li>
</ol><br />
<?php echo CHtml::form(array("admin/dataentry/sa/iteratesurvey/unfinalizeanswers/true/surveyid/".$surveyid), 'post');?>
<input class='btn btn-danger' type='submit' onclick="return confirm('<?php eT("Are you really sure you want to *delete* some incomplete answers and reset the completed state of both answers and tokens?","js");?>')" value='<?php eT("Reset answers and token completed state");?>' />
</form>
</div>
<?php }?>
</div>
