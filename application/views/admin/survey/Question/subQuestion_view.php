<script type='text/javascript'>
    var sLabelSetName='<?php $clang->eT('Label set name','js'); ?>';
    var languagecount=<?php echo count($anslangs); ?>;
    var newansweroption_text='<?php $clang->eT('New subquestion','js'); ?>';
    var strcode='<?php $clang->eT('Code','js'); ?>';
    var strlabel='<?php $clang->eT('Label','js'); ?>';
    var strCantDeleteLastAnswer='<?php $clang->eT('You cannot delete the last subquestion.','js'); ?>';
    var lsbrowsertitle='<?php $clang->eT('Label set browser','js'); ?>';
    var quickaddtitle='<?php $clang->eT('Quick-add subquestions','js'); ?>';
    var duplicatesubquestioncode='<?php $clang->eT('Error: You are trying to use duplicate subquestion codes.','js'); ?>';
    var strNoLabelSet='<?php $clang->eT('There are no label sets which match the survey default language','js'); ?>';
    var langs='<?php echo implode(';',$anslangs); ?>';
    var otherisreserved='<?php $clang->eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var sImageURL ='<?php echo Yii::app()->getConfig('adminimageurl'); ?>';
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
<?php echo PrepareEditorScript(); ?>
<div class='header ui-widget-header'>
    <?php $clang->eT("Edit subquestions"); ?>
</div>
<?php echo CHtml::form(array("admin/database"), 'post', array('id'=>'editsubquestionsform', 'name'=>'editsubquestionsform')); ?>

    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
    <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
    <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
    <input type='hidden' id='action' name='action' value='updatesubquestions' />
    <input type='hidden' id='sortorder' name='sortorder' value='' />
    <input type='hidden' id='deletedqids' name='deletedqids' value='' />
    <div id='tabs'>
        <ul>
            <?php foreach ($anslangs as $anslang)
                { ?>
                <li><a href='#tabpage_<?php echo $anslang; ?>'><?php echo getLanguageNameFromCode($anslang, false); ?>
                        <?php if ($anslang==Survey::model()->findByPk($surveyid)->language) { ?> (<?php echo $clang->gT("Base language"); ?>) <?php } ?></a>
                </li>
                <?php } ?>
        </ul>
        <?php
            $first=true;
            $sortorderids='';
            $codeids='';
        ?>

        <?php foreach ($anslangs as $anslang)
            { ?>
            <div id='tabpage_<?php echo $anslang; ?>' class='tab-page'>
                <?php for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
                    {
                        $position=0;
                        if ($scalecount>1)
                        {
                            if ($scale_id==0)
                            { ?>
                            <div class='header ui-widget-header'>
                            <?php $clang->eT("Y-Scale"); ?></div>
                            <?php }
                            else
                            { ?>
                            <div class='header ui-widget-header'>
                            <?php $clang->eT("X-Scale"); ?></div>
                            <?php }
                        }
                        $result = $results[$anslang][$scale_id];
                        $anscount = count($result);
                    ?>
                    <table class='answertable' id='answertable_<?php echo $anslang; ?>_<?php echo $scale_id; ?>'>
                        <thead>
                            <tr><th>&nbsp;</th>
                                <th><?php $clang->eT("Code"); ?></th>
                                <th><?php $clang->eT("Subquestion"); ?></th>
                                <?php if ($activated != 'Y' && $first)
                                    { ?>
                                    <th><?php $clang->eT("Action"); ?></th>
                                    <?php } ?>
                            </tr></thead>
                        <tbody>
                            <?php $alternate=false;
                                foreach ($result as $row)
                                {
                                    $row->title = htmlspecialchars($row->title);
                                    $row->question=htmlspecialchars($row->question);

                                    if ($first) {$codeids=$codeids.' '.$row->question_order;}
                                ?>
                                <tr id='row_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                    <?php if ($alternate==true)
                                        { ?>
                                        class="highlight"
                                        <?php $alternate=false;
                                        }
                                        else
                                        {
                                            $alternate=true;
                                        }
                                    ?>
                                    ><td>

                                        <?php if ($activated == 'Y' ) // if activated
                                            { ?>
                                            &nbsp;</td><td><input type='hidden' name='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" maxlength='20' size='5'
                                                /><?php echo $row->title; ?>
                                            <?php }
                                            elseif ($activated != 'Y' && $first) // If survey is not activated and first language
                                            { ?>
                                            <?php if($row->title) {$sPattern="^([a-zA-Z0-9]*|{$row->title})$";}else{$sPattern="^[a-zA-Z0-9]*$";} ?>
                                            <img class='handle' src='<?php echo $sImageURL; ?>handle.png' alt=''/></td>
                                            <td><input type='hidden' class='oldcode' id='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" />
                                            <input type='text' id='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' class='code' name='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" maxlength='20' size='5' pattern='<?php echo $sPattern; ?>' required='required' />

                                            <?php }
                                            else
                                            { ?>
                                        </td><td><?php echo $row->title; ?>

                                            <?php } ?>

                                    </td><td>
                                        <input type='text' size='100' class='answer' id='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' placeholder='<?php $clang->eT("Some example subquestion","js") ?>' value="<?php echo $row->question; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}" />
                                        <?php echo  getEditor("editanswer","answer_".$row->language."_".$row->qid."_{$row->scale_id}", "[".$clang->gT("Subquestion:", "js")."](".$row->language.")",$surveyid,$gid,$qid,'editanswer'); ?>
                                    </td>
                                    <td>


                                        <?php if ($activated != 'Y' && $first)
                                            { ?>
                                            <img src='<?php echo $sImageURL; ?>addanswer.png' class='btnaddanswer' alt='<?php $clang->eT("Insert a new subquestion after this one") ?>' />
                                            <img src='<?php echo $sImageURL; ?>deleteanswer.png' class='btndelanswer' alt='<?php $clang->eT("Delete this subquestion") ?>' />
                                            <?php } ?>

                                    </td></tr>
                                <?php $position++;
                                }
                                ++$anscount; ?>
                        </tbody></table>
                    <?php $disabled='';
                        if ($activated == 'Y')
                        {
                            $disabled="disabled='disabled'";
                    } ?>
                    <div class="action-buttons">
                        <button class='btnlsbrowser' id='btnlsbrowser_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php $clang->eT('Predefined label sets...'); ?></button>
                        <button class='btnquickadd' id='btnquickadd_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php $clang->eT('Quick add...'); ?></button>
                        <?php if(Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('labelsets','create')){ ?>
                        <button class='bthsaveaslabel' id='bthsaveaslabel_<?php echo $scale_id; ?>' type='button'><?php $clang->eT('Save as label set'); ?></button>
                        <?php } ?>
                    </div>

                    <?php }

                    $first=false; ?>
            </div>
            <?php } ?>
        <div id='labelsetbrowser' class='labelsets-update' style='display:none;'>
            <div style='float:left; width:260px;'>
                <label for='labelsets'><?php $clang->eT('Available label sets:'); ?></label>
                <select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                <p class='button-list'>
                    <button id='btnlsreplace' type='button'><?php $clang->eT('Replace'); ?></button>
                    <button id='btnlsinsert' type='button'><?php $clang->eT('Add'); ?></button>
                    <button id='btncancel' type='button'><?php $clang->eT('Cancel'); ?></button>
                </p>
            </div>
            <div id='labelsetpreview' style='float:right;width:500px;'>
            </div>
        </div>
        <div id='quickadd' class='labelsets-update' style='display:none;'>
            <div style='float:left;'>
                <label for='quickaddarea'><?php $clang->eT('Enter your subquestions:'); ?></label>
                <textarea id='quickaddarea' class='tipme' title='<?php $clang->eT('Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.'); ?>' cols='100' rows='30' style='width:570px;'></textarea>
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
                <label for="replacelabel"><?php $clang->eT('Replace existing label set'); ?></label>
            </p>
            <p class='button-list'>
                <button id='btnsave' type='button'><?php $clang->eT('Save'); ?></button>
                <button id='btnlacancel' type='button'><?php $clang->eT('Cancel'); ?></button>
            </p>
        </div>
        <div id="dialog-confirm-replace" title="<?php $clang->eT('Replace label set?'); ?>" style='display:none;'>
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php $clang->eT('You are about to replace a given label set with the labels of this subquestions. Continue?'); ?></p>
        </div>

        <div id="dialog-duplicate" title="<?php $clang->eT('Duplicate label set name'); ?>" style='display:none;'>
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php $clang->eT('Sorry, the name you entered for the label set is already in the database. Please select a different name.'); ?></p>
        </div>

        <div id="dialog-result" title="Query Result" style='display:none;'>

        </div>
        <p>
            <input type='submit' id='saveallbtn_<?php echo $anslang; ?>' name='method' value='<?php $clang->eT("Save changes"); ?>' />
            <?php $position=sprintf("%05d", $position); ?>
            <?php if ($activated == 'Y')
                { ?>
                <br />
                <font color='red' size='1'><i><strong>
                        <?php $clang->eT("Warning"); ?></strong>: <?php $clang->eT("You cannot add/remove subquestions or edit their codes because the survey is active."); ?></i></font>
                <?php } ?>
        </p>
    </div>
    <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />
</form>
