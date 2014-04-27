<div class='header ui-widget-header'><?php eT("Token export options"); ?></div><br />
<?php echo CHtml::form(array("admin/tokens/sa/exportdialog/surveyid/{$surveyid}"), 'post', array('id'=>'exportdialog', 'name'=>'exportdialog')); ?>
    <ul><li><label for='tokenstatus'><?php eT('Token status:'); ?></label><select id='tokenstatus' name='tokenstatus' >
                <option selected='selected' value='0'><?php eT('All tokens'); ?></option>
                <option value='1'><?php eT('Completed'); ?></option>
                <option value='2'><?php eT('Not started'); ?></option>
                <?php
                if ($thissurvey['anonymized'] == 'N')
                {
                    echo "<option value='3'>" . gT('Started but not yet completed') . "</option>";
                }
                ?>
            </select></li>
        <li><label for='invitationstatus'><?php eT('Invitation status:'); ?></label><select id='invitationstatus' name='invitationstatus' >
                <option selected='selected' value='0'><?php eT('All'); ?></option>
                <option value='1'><?php eT('Invited'); ?></option>
                <option value='2'><?php eT('Not invited'); ?></option>
            </select></li>
        <li><label for='reminderstatus'><?php eT('Reminder status:'); ?></label><select id='reminderstatus' name='reminderstatus' >
                <option selected='selected' value='0'><?php eT('All'); ?></option>
                <option value='1'><?php eT('Reminder(s) sent'); ?></option>
                <option value='2'><?php eT('No reminder(s) sent'); ?></option>
            </select></li>
        <li><label for='tokenlanguage' ><?php eT('Filter by language'); ?></label><select id='tokenlanguage' name='tokenlanguage' >
                <option selected='selected' value=''><?php eT('All'); ?></option>
<?php
if($resultr){
    foreach ($resultr as $lrow)
    {
        echo "<option value='{$lrow->language}'>" . getLanguageNameFromCode($lrow->language,false) . "</option>";
    }
}
?>
            </select></li>
        <li><label for='filteremail' ><?php eT('Filter by email address'); ?></label><input type='email' id='filteremail' name='filteremail' /></li>
        <li>&nbsp;</li>
        <li><label for='tokendeleteexported' ><?php eT('Delete exported tokens'); ?></label><input type='checkbox' id='tokendeleteexported' name='tokendeleteexported' /> </li>
    </ul>
    <p><input type='submit' name='submit' value='<?php eT('Export tokens'); ?>' />
        <input type='hidden' name='action' id='action' value='tokens' />
        <input type='hidden' name='sid' id='sid' value='<?php echo $surveyid; ?>' />
        <input type='hidden' name='subaction' id='subaction' value='export' />
    </p></form>
