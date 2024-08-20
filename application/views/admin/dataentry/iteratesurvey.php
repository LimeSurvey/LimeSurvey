<div class="side-body">
<h3><?php eT("Iterate survey");?></h3>

<?php if($success) {?>
<p style='width:100%;'>
<font class='successtitle'><?php eT("Success");?></font><br />
<?php eT("Responses and participants have been re-opened.");?><br />
<?php } else { ?>
<div class='messagebox ui-corner-all'><div class='header ui-widget-header'><?php eT("Important instructions");?></div>
<br/><?php eT("Click on the following button if you want to");?>:<br />
<ol>
<li><?php eT("Delete all incomplete responses that correspond to a participant for which a completed response is already recorded");?></li>
<li><?php eT("Reset completed responses to the incomplete state");?></li>
<li><?php eT("Reset all your participants to the 'not used' state");?></li>
</ol><br />
<?php echo CHtml::form(array("admin/dataentry/sa/iteratesurvey/unfinalizeanswers/true/surveyid/".$surveyid), 'post');?>
<input 
    class='btn btn-danger'
    type='submit' 
    onclick="return confirm('<?php eT("Are you really sure you want to delete incomplete responses and reset the completed state of both, response and participant?","js");?>')" value='<?php eT("Reset answers and participants completed state");?>' />
</form>
</div>
<?php }?>
</div>
