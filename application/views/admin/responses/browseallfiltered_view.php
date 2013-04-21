<div class='header ui-widget-header'
<?php $clang->eT("Browse responses"); ?>:</strong> <?php echo $surveyinfo['name']; ?>
</div>
<p>
    <?php $clang->eT("Showing Filtered Results"); ?><br />
</p>
    <?php echo CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}"), 'post'); ?>
<p>
        <input type='submit' value='<?php $clang->eT("Clear filter");?>'>
        </p>
        <input type='hidden' name='sid' value='$surveyid' />
        <input type='hidden' name='clearsqlfilter' value='1' />
        <input type='hidden' name='subaction' value='all' />
    </form>
