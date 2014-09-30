<?php echo PrepareEditorScript(true, $this); ?>
<div class='header ui-widget-header'>
    <?php $clang->eT("Edit answer options"); ?>
</div>
<?php echo CHtml::form(array("admin/database"), 'post', array('id'=>'editanswersform', 'name'=>'editanswersform')); ?>
    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
    <input type='hidden' name='action' value='updateansweroptions' />
    <input type='hidden' name='sortorder' value='' />
    <?php $first=true; ?>
    <script type='text/javascript'>
        var languagecount=<?php echo count($anslangs); ?>;
        var scalecount=<?php echo $scalecount; ?>;
        var assessmentvisible=<?php echo $assessmentvisible?'true':'false'; ?>;
        var newansweroption_text='<?php $clang->eT('New answer option','js'); ?>';
        var sLabelSetName='<?php $clang->eT('Label set name','js'); ?>';
        var strcode='<?php $clang->eT('Code','js'); ?>';
        var strlabel='<?php $clang->eT('Label','js'); ?>';
        var strCantDeleteLastAnswer='<?php $clang->eT('You cannot delete the last answer option.','js'); ?>';
        var lsbrowsertitle='<?php $clang->eT('Label set browser','js'); ?>';
        var quickaddtitle='<?php $clang->eT('Quick-add answers','js'); ?>';
        var sAssessmentValue='<?php $clang->eT('Assessment value','js'); ?>';
        var duplicateanswercode='<?php $clang->eT('Error: You are trying to use duplicate answer codes.','js'); ?>';
        var strNoLabelSet='<?php $clang->eT('There are no label sets which match the survey default language','js'); ?>';
        var langs='<?php echo implode(';',$anslangs); ?>';
        var sImageURL ="<?php echo Yii::app()->getConfig('adminimageurl'); ?>";
        var saveaslabletitle  = '<?php $clang->eT('Save as label set','js'); ?>';
        var lanameurl = '<?php echo Yii::app()->createUrl('/admin/labels/sa/getAllSets'); ?>';
        var lasaveurl = '<?php echo Yii::app()->createUrl('/admin/labels/sa/ajaxSets'); ?>';
        var lsdetailurl = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetdetails'); ?>';
        var lspickurl = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetpicker'); ?>';
        var check = true;
        var lasuccess = '<?php $clang->eT('The records have been saved successfully!'); ?>';
        var lafail = '<?php $clang->eT('Sorry, the request failed!'); ?>';
        var ok = '<?php $clang->eT('Ok'); ?>';
        var cancel = '<?php $clang->eT('Cancel'); ?>';
    </script>
    <div id='tabs'>
    <ul>
        <?php foreach ($anslangs as $anslang)
            { ?>
            <li><a href='#tabpage_<?php echo $anslang; ?>'><?php echo getLanguageNameFromCode($anslang, false); ?>
                    <?php if ($anslang==Survey::model()->findByPk($surveyid)->language) { ?> (<?php $clang->eT("Base language"); ?>) <?php } ?></a>
            </li>
            <?php } ?>
    </ul>

    <?php foreach ($anslangs as $anslang)
        { ?>
        <div id='tabpage_<?php echo $anslang; ?>' class='tab-page'>

            <?php for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
                {
                    $position=1;
                    if ($scalecount>1)
                    { ?>
                    <div class='header ui-widget-header' style='margin-top:5px;'><?php echo sprintf($clang->gT("Answer scale %s"),$scale_id+1); ?></div>
                    <?php } ?>


                <table class='answertable' id='answers_<?php echo $anslang; ?>_<?php echo $scale_id; ?>'>
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php $clang->eT("Code"); ?></th>
                            <?php if ($assessmentvisible)
                                { ?>
                                <th><?php $clang->eT("Assessment value"); ?>
                                <?php }
                                else
                                { ?>
                                <th style='display:none;'>&nbsp;
                                    <?php } ?>

                            </th>
                            <th><?php $clang->eT("Answer option"); ?></th>
                            <th><?php $clang->eT("Actions"); ?></th>
                        </tr></thead>
                    <tbody>
                    <?php $alternate=true;

                        $query = "SELECT * FROM {{answers}} WHERE qid='{$qid}' AND language='{$anslang}' and scale_id=$scale_id ORDER BY sortorder, code";
                        $result = dbExecuteAssoc($query);
                        $aResults= $result->readAll();
                        $anscount = count($aResults);

                        foreach ($aResults as $row)
                        {
                            $row['code'] = htmlspecialchars($row['code']);
                            $row['answer']=htmlspecialchars($row['answer']);
                        ?>
                        <tr class='row_<?php echo $position; ?><?php if ($alternate==true){ ?> highlight<?php } ?><?php $alternate=!$alternate; ?>'><td>
                                <?php if ($first)
                                    { ?>
                                    <img class='handle' src='<?php echo $sImageURL; ?>handle.png' alt=''/></td>
                                    <td><input type='hidden' class='oldcode' id='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['code']; ?>" /><input type='text' class='code' id='code_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['code']; ?>" maxlength='5' size='5' required
                                        onkeypress="return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')"
                                        />
                                    <?php }
                                    else
                                    { ?>
                                    &nbsp;</td><td><?php echo $row['code']; ?>

                                    <?php } ?>

                            </td>
                            <td

                                <?php if ($assessmentvisible && $first)
                                    { ?>
                                    ><input type='text' class='assessment' id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['assessment_value']; ?>" maxlength='5' size='5'
                                        onkeypress="return goodchars(event,'-1234567890')"
                                        />
                                    <?php }
                                    elseif ( $first)
                                    { ?>
                                    style='display:none;'><input type='hidden' class='assessment' id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['assessment_value']; ?>"
                                        onkeypress="return goodchars(event,'-1234567890')"
                                        />
                                    <?php }
                                    elseif ($assessmentvisible)
                                    { ?>
                                    ><?php echo $row['assessment_value']; ?>
                                    <?php }
                                    else
                                    { ?>
                                    style='display:none;'>
                                    <?php } ?>

                            </td><td>
                                <input type='text' class='answer' id='answer_<?php echo $row['language']; ?>_<?php echo $row['sortorder']; ?>_<?php echo $scale_id; ?>' name='answer_<?php echo $row['language']; ?>_<?php echo $row['sortorder']; ?>_<?php echo $scale_id; ?>' size='100' placeholder='<?php $clang->eT("Some example answer option","js") ?>' value="<?php echo $row['answer']; ?>" />
                                <?php echo  getEditor("editanswer","answer_".$row['language']."_{$row['sortorder']}_{$scale_id}", "[".$clang->gT("Answer:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer'); ?>


                            </td><td><?php if ($first) { ?>
                                <img src='<?php echo $sImageURL; ?>addanswer.png' class='btnaddanswer' alt='<?php $clang->eT("Insert a new answer option after this one") ?>' />
                                <img src='<?php echo $sImageURL; ?>deleteanswer.png' class='btndelanswer' alt='<?php $clang->eT("Delete this answer option") ?>' />
                                <?php } ?>
                            </td></tr>
                        <?php $position++;
                    } ?>
                </table>
                <?php if ($first)
                    { ?>
                    <input type='hidden' id='answercount_<?php echo $scale_id; ?>' name='answercount_<?php echo $scale_id; ?>' value='<?php echo $anscount; ?>' />
                    <?php } ?>
                <div class="action-buttons">
                    <button id='btnlsbrowser_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btnlsbrowser' type='button'><?php $clang->eT('Predefined label sets...'); ?></button>
                    <button id='btnquickadd_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btnquickadd' type='button'><?php $clang->eT('Quick add...'); ?></button>

                    <?php if(Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('labelsets','create')) { //){ ?>
                    <button class='bthsaveaslabel' id='bthsaveaslabel_<?php echo $scale_id; ?>' type='button'><?php $clang->eT('Save as label set'); ?></button>

                   <?php } ?>
                </div>

                <?php }

                $position=sprintf("%05d", $position);

                $first=false; ?>
        </div>
        <?php } ?>
    <div id='labelsetbrowser' class='labelsets-update' style='display:none;'><div style='float:left;width:260px;'>
        <label for='labelsets'><?php $clang->eT('Available label sets:'); ?></label>
        <select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
        <p class='button-list'>
        <button id='btnlsreplace' type='button'><?php $clang->eT('Replace'); ?></button>
        <button id='btnlsinsert' type='button'><?php $clang->eT('Add'); ?></button>
        <button id='btncancel' type='button'><?php $clang->eT('Cancel'); ?></button>
        </p>
        </div>

        <div id='labelsetpreview' style='float:right;width:500px;'></div>
        </div>
        <div id='quickadd' class='labelsets-update' style='display:none;'><div style='float:left;'>
        <label for='quickaddarea'><?php $clang->eT('Enter your answers:'); ?></label>
        <textarea id='quickaddarea' class='tipme' title='<?php $clang->eT('Enter one answer per line. You can provide a code by separating code and answer text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.'); ?>' cols='100' rows='30' style='width:570px;'></textarea>
        <p class='button-list'>
        <button id='btnqareplace' type='button'><?php $clang->eT('Replace'); ?></button>
        <button id='btnqainsert' type='button'><?php $clang->eT('Add'); ?></button>
        <button id='btnqacancel' type='button'><?php $clang->eT('Cancel'); ?></button>
        </p>
        </div>
        </div>
        <div id="saveaslabel" style='display:none;'>
            <p>
                <input type="radio" name="savelabeloption" id="newlabel">
                <label for="newlabel"><?php $clang->eT('New label set'); ?></label>
            </p>
            <p>
                <input type="radio" name="savelabeloption" id="replacelabel">
                <label for="replacelabel"><?php $clang->eT('Replace existing label set'); ?>
            </p>
            <p class='button-list'>
                <button id='btnsave' type='button'><?php $clang->eT('Save'); ?></button>
                <button id='btnlacancel' type='button'><?php $clang->eT('Cancel'); ?></button>
            </p>
        </div>

        <div id="dialog-confirm-replace" title="<?php $clang->eT('Replace label set?'); ?>" style='display:none;'>
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php $clang->eT('You are about to replace a given label set with the current answer options. Continue?'); ?></p>
        </div>

        <div id="dialog-duplicate" title="<?php $clang->eT('Duplicate label set name'); ?>" style='display:none;'>
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php $clang->eT('Sorry, the name you entered for the label set is already in the database. Please select a different name.'); ?></p>
        </div>

        <div id="dialog-result" title="Query Result" style='display:none;'>

        </div>

        <p><input type='submit' id='saveallbtn_<?php echo $anslang; ?>' name='method' value='<?php $clang->eT("Save changes"); ?>' />
    </div>
    <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />
</form>
