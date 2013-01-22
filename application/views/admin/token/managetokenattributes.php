<div class='header ui-widget-header'><?php $clang->eT("Manage token attribute fields"); ?></div>

<?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributedescriptions/surveyid/{$surveyid}"), 'post'); ?>
    <div id="tabs">
        <ul>
        <?php
        foreach ($languages as $language)
        {
            $tab_title = getLanguageNameFromCode($language, false);
            if ($language == Survey::model()->findByPk($iSurveyID)->language)
			{
                $tab_title .= '(' . $clang->gT("Base language") . ')';
			}
        ?>
            <li><a href="#language_<?php echo $language ?>"><?php echo $tab_title; ?></a></li>
        <?php
        }
        ?>
        </ul>
<?php
    foreach ($languages as $language)
    {
?>
        <div id="language_<?php echo $language ?>">
            <table class='listsurveys'>
                <tr>
                    <th><?php $clang->eT("Attribute field"); ?></th>
                    <th><?php $clang->eT("Field description"); ?></th>
                    <th><?php $clang->eT("Mandatory?"); ?></th>
                    <th><?php $clang->eT("Show during registration?") ?></th>
                    <th><?php $clang->eT("Field caption"); ?></th>
                    <th><?php $clang->eT("Example data"); ?></th>
                </tr>


        <?php
        $nrofattributes = 0;
        foreach ($tokenfields as $tokenfield)
        {
            if (isset($tokenfielddata[$tokenfield]))
                $tokenvalues = $tokenfielddata[$tokenfield];
            else
                $tokenvalues = array(
                    'description' => '',
                    'mandatory' => 'N',
                    'show_register' => 'N',
                );
            $nrofattributes++;
            echo "
                <tr>
                    <td>$tokenfield</td>";
            if ($language == $thissurvey['language'])
            {
                echo "
                    <td><input type='text' name='description_$tokenfield' value='" . htmlspecialchars($tokenvalues['description'], ENT_QUOTES, 'UTF-8') . "' /></td>
                    <td><input type='checkbox' name='mandatory_$tokenfield' value='Y'";
                if ($tokenvalues['mandatory'] == 'Y')
                    echo ' checked="checked"';
                echo " /></td>
                    <td><input type='checkbox' name='show_register_$tokenfield' value='Y'";
                if (!empty($tokenvalues['show_register']) && $tokenvalues['show_register'] == 'Y')
                    echo ' checked="checked"';
                echo " /></td>";
            }
            else
            {
                echo "
                    <td>", htmlspecialchars($tokenvalues['description'], ENT_QUOTES, 'UTF-8'), "</td>
                    <td>", $tokenvalues['mandatory'] == 'Y' ? $clang->eT('Yes') : $clang->eT('No'), "</td>
                    <td>", $tokenvalues['show_register'] == 'Y' ? $clang->eT('Yes') : $clang->eT('No'), "</td>";
            }
            echo "
                <td><input type='text' name='caption_{$tokenfield}_$language' value='" . htmlspecialchars(!empty($tokencaptions[$language][$tokenfield]) ? $tokencaptions[$language][$tokenfield] : '', ENT_QUOTES, 'UTF-8') . "' /></td>
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
    </table></div>
<?php
    }
?>
    </div>
    <p>
        <input type="submit" value="<?php $clang->eT('Save'); ?>" />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
    </p>
</form>

<br /><br />

<div class='header ui-widget-header'><?php $clang->eT("Add token attributes"); ?></div><p>

<?php echo sprintf($clang->gT('There are %s user attribute fields in this token table'), $nrofattributes); ?></p>
<?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributes/surveyid/{$surveyid}"), 'post',array('id'=>'addattribute')); ?>
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
