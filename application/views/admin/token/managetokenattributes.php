<?php if( count($tokenfieldlist)) { ?>
    <div class='header ui-widget-header'><?php $clang->eT("Manage token attribute fields"); ?></div>
    <?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributedescriptions/surveyid/{$surveyid}"), 'post'); ?>
    <div id="tabs">
        <ul>
            <?php foreach ($languages as $sLanguage) {
                    $sTabTitle = getLanguageNameFromCode($sLanguage, false);
                    if ($sLanguage == Survey::model()->findByPk($iSurveyID)->language)
                    {
                        $sTabTitle .= '(' . $clang->gT("Base language") . ')';
                    }
                ?>
                <li><a href="#language_<?php echo $sLanguage ?>"><?php echo $sTabTitle; ?></a></li>
                <?php } ?>
        </ul>
        <?php foreach ($languages as $sLanguage) { ?>
            <div id="language_<?php echo $sLanguage ?>">
                <table class='listtokenattributes'>
                    <thead> <tr>
                        <th><?php $clang->eT("Attribute field"); ?></th>
                        <th><?php $clang->eT("Field description"); ?></th>
                        <th><?php $clang->eT("Mandatory?"); ?></th>
                        <th><?php $clang->eT("Show during registration?") ?></th>
                        <th><?php $clang->eT("Field caption"); ?></th>
                        <th><?php $clang->eT("CPDB mapping"); ?></th>
                        <th><?php $clang->eT("Example data"); ?></th>
                    </tr> </thead>
                    <tbody>
                    <?php $nrofattributes = 0;
                        foreach ($tokenfields as $sTokenField) {
                            if (isset($tokenfielddata[$sTokenField]))
                                $tokenvalues = $tokenfielddata[$sTokenField];
                            else
                                $tokenvalues = array('description' => '','mandatory' => 'N','show_register' => 'N','cpdbmap'=>'');
                            $nrofattributes++;
                            echo "
                            <tr>
                            <td>{$sTokenField}</td>";                                                        
                            if ($sLanguage == $thissurvey['language'])
                            {
                                echo "<td><input type='text' name='description_{$sTokenField}' value='" . htmlspecialchars($tokenvalues['description'], ENT_QUOTES, 'UTF-8') . "' /></td>";
                                echo "<td><input type='checkbox' name='mandatory_{$sTokenField}' value='Y'";
                                if ($tokenvalues['mandatory'] == 'Y')
                                    echo ' checked="checked"';
                                echo " /></td>
                                <td><input type='checkbox' name='show_register_{$sTokenField}' value='Y'";
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
                            }; ?>
                        <td><input type='text' name='caption_<?php echo $sTokenField; ?>_<?php echo $sLanguage; ?>' value='<?php echo htmlspecialchars(!empty($tokencaptions[$sLanguage][$sTokenField]) ? $tokencaptions[$sLanguage][$sTokenField] : '', ENT_QUOTES, 'UTF-8'); ?>' /></td>
                        <td><?php 
                            if ($sLanguage == $thissurvey['language'])
                            {
                                echo CHtml::dropDownList('cpdbmap_'.$sTokenField,$tokenvalues['cpdbmap'],$aCPDBAttributes);
                            }
                            else
                            {
                                echo $aCPDBAttributes[$tokenvalues['cpdbmap']];
                            }
                        ?></td>
                        <td>
                        <?php 
                            if ($examplerow !== false)
                            {
                                echo htmlspecialchars($examplerow[$sTokenField]);
                            }
                            else
                            {
                                $clang->gT('<no data>');
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    ?>
                    <tbody></table></div>
            <?php } ?>
    </div>
    <p>
        <input type="submit" value="<?php $clang->eT('Save'); ?>" />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
    </p>
    </form>

    <br /><br />
    <?php } ?>
<div class='header ui-widget-header'><?php $clang->eT("Add or delete token attributes"); ?></div>
<p><?php echo sprintf($clang->gT('There are %s user attribute fields in this token table'), $nrofattributes); ?></p>
<?php echo CHtml::form(array("admin/tokens/sa/updatetokenattributes/surveyid/{$surveyid}"), 'post',array('id'=>'addattribute')); ?>
<p>
    <label for="addnumber"><?php $clang->eT('Number of attribute fields to add:'); ?></label>
    <input type="text" id="addnumber" name="addnumber" size="3" maxlength="3" value="1" />
</p>
<p>
    <?php echo CHtml::submitButton($clang->gT('Add fields')); ?>
    <?php echo CHtml::hiddenField('action','tokens'); ?>
    <?php echo CHtml::hiddenField('subaction','updatetokenattributes'); ?>
    <?php echo CHtml::hiddenField('sid',$surveyid); ?>
</p>
<?php echo CHtml::endForm() ?>
<?php if( count($tokenfieldlist)) { ?>
    <?php echo CHtml::form(array("admin/tokens/sa/deletetokenattributes/surveyid/{$surveyid}"), 'post',array('id'=>'attributenumber')); ?>
    <p>
        <label for="deleteattribute"><?php $clang->eT('Delete this attribute:'); ?></label>
        <?php  echo CHtml::dropDownList('deleteattribute',"",CHtml::listData($tokenfieldlist,'id','descrition'),array('empty' => $clang->gT('none'))); ?>
    </p>
    <p>
        <?php echo CHtml::submitButton($clang->gT('Delete attribute')); ?>
        <?php echo CHtml::hiddenField('action','tokens'); ?>
        <?php echo CHtml::hiddenField('subaction','deletetokenattributes'); ?>
        <?php echo CHtml::hiddenField('sid',$surveyid); ?>
    </p>
    <?php echo CHtml::endForm() ?>
    <?php } ?>
<br /><br />
