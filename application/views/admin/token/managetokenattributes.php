<div class='header ui-widget-header'><?php $clang->eT("Manage token attribute fields"); ?></div>

<form action="<?php echo $this->createUrl("admin/tokens/updatetokenattributedescriptions/surveyid/$surveyid"); ?>" method="post">
    <table class='listsurveys'>
        <tr>
            <th><?php $clang->eT("Attribute field"); ?></th>
            <th><?php $clang->eT("Field description"); ?></th>
            <th><?php $clang->eT("Field caption"); ?></th>
            <th><?php $clang->eT("Mandatory?"); ?></th>
            <th><?php $clang->eT("Example data"); ?></th>
        </tr>


        <?php
        foreach ($tokenfields as $tokenfield => $tokenvalues)
        {
            $nrofattributes++;
            echo "<tr>
                <td>$tokenfield</td>
                <td><input type='text' name='description_$tokenfield' value='" . htmlspecialchars($tokenvalues['tokendescription'], ENT_QUOTES, 'UTF-8') . "' /></td>
                <td><input type='text' name='caption_$tokenfield' value='" . htmlspecialchars($tokenvalues['tokencaption'], ENT_QUOTES, 'UTF-8') . "' /></td>
                <td><input type='checkbox' name='mandatory_$tokenfield' value='Y'";
                    if($tokenvalues['tokenmandatory'] == 'Y')
                        echo ' selected="selected"';
           echo " /></td>
                <td>";
                    if ($examplerow !== false)
                    {
                        if (!$tokenfield[10] == 'c')
                        {
                            echo htmlspecialchars($examplerow[$tokenfield]);
                        }
                    }
                    else
                    {
                        $clang->gT('<no data>');
                    }
            echo "</td></tr>";
        }
        ?>
    </table><p>

        <input type="submit" value="<?php $clang->eT('Save'); ?>" />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
</form>

<br /><br />

<div class='header ui-widget-header'><?php $clang->eT("Add token attributes"); ?></div><p>

<?php echo sprintf($clang->gT('There are %s user attribute fields in this token table'), $nrofattributes); ?></p>
<form id="addattribute" action="<?php echo $this->createUrl("admin/tokens/updatetokenattributes/surveyid/$surveyid"); ?>" method="post">
    <p>
        <label for="addnumber"><?php $clang->eT('Number of attribute fields to add:'); ?></label>
        <input type="text" id="addnumber" name="addnumber" size="3" maxlength="3" value="1" />
    </p>
    <p>
        <input type="submit" value="<?php $clang->eT('Add fields'); ?>" />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='subaction' value='updatetokenattributes' />
        <input type='hidden' name='sid' value="<?php echo $surveyid; ?>" />
    </p>
</form>
<br /><br />
