<div class='header ui-widget-header'><?php echo $clang->gT("Token export options"); ?></div>
<form id='exportdialog' name='exportdialog' action='<?php echo $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$surveyid"); ?>' method='post'>
    <ul><li><label for='tokenstatus'><?php echo $clang->gT('Token status:'); ?></label><select id='tokenstatus' name='tokenstatus' >
                <option selected='selected' value='0'><?php echo $clang->gT('All tokens'); ?></option>
                <option value='1'><?php echo $clang->gT('Completed'); ?></option>
                <option value='2'><?php echo $clang->gT('Not started'); ?></option>
                <?php
                if ($thissurvey['anonymized'] == 'N')
                {
                    echo "<option value='3'>" . $clang->gT('Started but not yet completed') . "</option>";
                }
                ?>
            </select></li>
        <li><label for='invitationstatus'><?php echo $clang->gT('Invitation status:'); ?></label><select id='invitationstatus' name='invitationstatus' >
                <option selected='selected' value='0'><?php echo $clang->gT('All'); ?></option>
                <option value='1'><?php echo $clang->gT('Invited'); ?></option>
                <option value='2'><?php echo $clang->gT('Not invited'); ?></option>
            </select></li>
        <li><label for='reminderstatus'><?php echo $clang->gT('Reminder status:'); ?></label><select id='reminderstatus' name='reminderstatus' >
                <option selected='selected' value='0'><?php echo $clang->gT('All'); ?></option>
                <option value='1'><?php echo $clang->gT('Reminder(s) sent'); ?></option>
                <option value='2'><?php echo $clang->gT('No reminder(s) sent'); ?></option>
            </select></li>
        <li><label for='tokenlanguage' ><?php echo $clang->gT('Filter by language'); ?></label><select id='tokenlanguage' name='tokenlanguage' >
                <option selected='selected' value=''><?php echo $clang->gT('All'); ?></option>
<?php
foreach ($resultr as $lrow)
{
    echo "<option value='{$lrow['language']}'>" . getLanguageNameFromCode($lrow['language']) . "</option>";
}
?>
            </select></li>
        <li><label for='filteremail' ><?php echo $clang->gT('Filter by email address'); ?></label><input type='text' id='filteremail' name='filteremail' /></li>
        <li>&nbsp;</li>
        <li><label for='tokendeleteexported' ><?php echo $clang->gT('Delete exported tokens'); ?></label><input type='checkbox' id='tokendeleteexported' name='tokendeleteexported' /> </li>
    </ul>
    <p><input type='submit' name='submit' value='<?php echo $clang->gT('Export tokens'); ?>' />
        <input type='hidden' name='action' id='action' value='tokens' />
        <input type='hidden' name='sid' id='sid' value='<?php echo $surveyid; ?>' />
        <input type='hidden' name='subaction' id='subaction' value='export' />
    </p></form>
