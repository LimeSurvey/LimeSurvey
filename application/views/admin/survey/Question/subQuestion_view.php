<script type='text/javascript'>
    var sLabelSetName='<?php eT('Label set name','js'); ?>';
    var languagecount=<?php echo count($anslangs); ?>;
    var newansweroption_text='<?php eT('New subquestion','js'); ?>';
    var strcode='<?php eT('Code','js'); ?>';
    var strlabel='<?php eT('Label','js'); ?>';
    var strCantDeleteLastAnswer='<?php eT('You cannot delete the last subquestion.','js'); ?>';
    var lsbrowsertitle='<?php eT('Label set browser','js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestions','js'); ?>';
    var duplicatesubquestioncode='<?php eT('Error: You are trying to use duplicate subquestion codes.','js'); ?>';
    var strNoLabelSet='<?php eT('There are no label sets which match the survey default language','js'); ?>';
    var langs='<?php echo implode(';',$anslangs); ?>';
    var otherisreserved='<?php ($aParentQuestion['other']=="Y") ? printf(gT("'%s' can not be used if question allow other.",'js'),'other') : ""; ?>';
    var sImageURL ='<?php echo Yii::app()->getConfig('adminimageurl'); ?>';
    var saveaslabletitle  = '<?php eT('Save as label set','js'); ?>';
    var lanameurl = '<?php echo Yii::app()->createUrl('/admin/labels/sa/getAllSets'); ?>';
    var lasaveurl = '<?php echo Yii::app()->createUrl('/admin/labels/sa/ajaxSets'); ?>';
    var sCheckLabelURL = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxchecklabel'); ?>';
    var lsdetailurl = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetdetails'); ?>';
    var lspickurl = '<?php echo Yii::app()->createUrl('/admin/questions/sa/ajaxlabelsetpicker'); ?>';
    var check = true;
    var lasuccess = '<?php eT('The records have been saved successfully!'); ?>';
    var lafail = '<?php eT('Sorry, the request failed!'); ?>';
    var ok = '<?php eT('Ok'); ?>';
    var cancel = '<?php eT('Cancel'); ?>';
</script>
<?php echo PrepareEditorScript(); ?>
<div class='header ui-widget-header'>
    <?php eT("Edit subquestions"); ?>
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
                        <?php if ($anslang==Survey::model()->findByPk($surveyid)->language) { ?> (<?php echo gT("Base language"); ?>) <?php } ?></a>
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
                            <?php eT("Y-Scale"); ?></div>
                            <?php }
                            else
                            { ?>
                            <div class='header ui-widget-header'>
                            <?php eT("X-Scale"); ?></div>
                            <?php }
                        }
                        $result = $results[$anslang][$scale_id];
                        $anscount = count($result);
                    ?>
                    <table class='answertable' id='answertable_<?php echo $anslang; ?>_<?php echo $scale_id; ?>'>
                        <thead>
                            <tr><th>&nbsp;</th>
                                <th><?php eT("Code"); ?></th>
                                <th><?php eT("Subquestion"); ?></th>
                                <?php if ($activated != 'Y' && $first)
                                    { ?>
                                    <th><?php eT("Action"); ?></th>
                                <?php } ?>
                                <?php if ($scale_id==0)
                                    { ?>
                                    <th class="relevancehead"><img src='<?php echo $sImageURL; ?>subq_relevance_en.png' class='btntogglerelevance' alt='<?php eT("Edit subquestion relevance") ?>'/> <span style="display: none" class="relevance"> <?php eT("Relevance"); ?> </span> </th>
                                <?php } ?>    
                            </tr></thead>
                        <tbody>
                            <?php $alternate=false;
                                foreach ($result as $row)
                                {
                                    $row->title = htmlspecialchars($row->title);
                                    $row->question=htmlspecialchars($row->question);
                                    $row->relevance=htmlspecialchars($row->relevance);

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
                                        <input type='text' size='100' class='answer' id='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' placeholder='<?php eT("Some example subquestion","js") ?>' value="<?php echo $row->question; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}" />
                                        
                                        <?php echo  getEditor("editanswer","answer_".$row->language."_".$row->qid."_{$row->scale_id}", "[".gT("Subquestion:", "js")."](".$row->language.")",$surveyid,$gid,$qid,'editanswer'); ?>
                                        </td>
                                        <?php if ($activated != 'Y' && $first)
                                            { ?>
                                            <td>
                                            <img src='<?php echo $sImageURL; ?>addanswer.png' class='btnaddanswer' alt='<?php eT("Insert a new subquestion after this one") ?>' />
                                            <img src='<?php echo $sImageURL; ?>deleteanswer.png' class='btndelanswer' alt='<?php eT("Delete this subquestion") ?>' />
                                            </td>
                                            <?php } ?>

                                    
                                    
                                  <?php if ($scale_id==0) {   /* relevance column */ ?>
                                            <td>
                                  <?php     if ($row->relevance!="1" && trim($row->relevance)!="") { ?> 
                                            <img src='<?php echo $sImageURL; ?>subq_relevance_en.png' class='btntogglerelevance' alt='<?php eT("Edit subquestion relevance") ?>'/>
                                  <?php     } else {   /* no relevance equation: icon deactivated */  ?> 
                                            <img src='<?php echo $sImageURL; ?>subq_relevance_dis.png' class='btntogglerelevance' alt='<?php eT("Edit subquestion relevance") ?>'/>  
                                  <?php     }
                                            if ($first) {  /* default lang - input field */?> 
                                                <input style="display: none" type='text' size='20' class='relevance' id='relevance_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='relevance_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->relevance; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}" />
                                  <?php     } else {       /* additional language: just print rel. equation */  ?>
                                        <span style="display: none" class="relevance"> <?php echo $row->relevance; ?> </span>
                                  <?php     }   ?>
                                            </td>
                                  <?php } ?>

                                  
                                    </tr>
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
                        <button class='btnlsbrowser' id='btnlsbrowser_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php eT('Predefined label sets...'); ?></button>
                        <button class='btnquickadd' id='btnquickadd_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php eT('Quick add...'); ?></button>
                        <?php if(Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('labelsets','create')){ ?>
                        <button class='bthsaveaslabel' id='bthsaveaslabel_<?php echo $scale_id; ?>' type='button'><?php eT('Save as label set'); ?></button>
                        <?php } ?>
                    </div>

                    <?php }

                    $first=false; ?>
            </div>
            <?php } ?>
        <div id='labelsetbrowser' class='labelsets-update' style='display:none;'>
            <div style='float:left; width:260px;'>
                <label for='labelsets'><?php eT('Available label sets:'); ?></label>
                <select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                <p class='button-list'>
                    <button id='btnlsreplace' type='button'><?php eT('Replace'); ?></button>
                    <button id='btnlsinsert' type='button'><?php eT('Add'); ?></button>
                    <button id='btncancel' type='button'><?php eT('Cancel'); ?></button>
                </p>
            </div>
            <div id='labelsetpreview' style='float:right;width:500px;'>
            </div>
        </div>
        <div id='quickadd' class='labelsets-update' style='display:none;'>
            <div style='float:left;'>
                <label for='quickaddarea'><?php eT('Enter your subquestions:'); ?></label>
                <textarea id='quickaddarea' class='tipme' title='<?php eT('Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.'); ?>' cols='100' rows='30' style='width:570px;'></textarea>
                <p class='button-list'>
                    <button id='btnqareplace' type='button'><?php eT('Replace'); ?></button>
                    <button id='btnqainsert' type='button'><?php eT('Add'); ?></button>
                    <button id='btnqacancel' type='button'><?php eT('Cancel'); ?></button>
                </p>
            </div>
        </div>
        <div id="saveaslabel" style='display:none;'>
            <p>
                <input type="radio" name="savelabeloption" id="newlabel">
                <label for="newlabel"><?php eT('New label set'); ?></label>
            </p>
            <p>
                <input type="radio" name="savelabeloption" id="replacelabel">
                <label for="replacelabel"><?php eT('Replace existing label set'); ?></label>
            </p>
            <p class='button-list'>
                <button id='btnsave' type='button'><?php eT('Save'); ?></button>
                <button id='btnlacancel' type='button'><?php eT('Cancel'); ?></button>
            </p>
        </div>
        <div id="dialog-confirm-replace" title="<?php eT('Replace label set?'); ?>" style='display:none;'>
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><span id='strReplaceMessage'></span></p>
        </div>

        <div id="dialog-duplicate" title="<?php eT('Duplicate label set name'); ?>" style='display:none;'>
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php eT('Sorry, the name you entered for the label set is already in the database. Please select a different name.'); ?></p>
        </div>

        <div id="dialog-result" title="Query Result" style='display:none;'>

        </div>
        <p>
            <input type='submit' id='saveallbtn_<?php echo $anslang; ?>' name='method' value='<?php eT("Save changes"); ?>' />
            <?php $position=sprintf("%05d", $position); ?>
            <?php if ($activated == 'Y')
                { ?>
                <br />
                <font color='red' size='1'><i><strong>
                        <?php eT("Warning"); ?></strong>: <?php eT("You cannot add/remove subquestions or edit their codes because the survey is active."); ?></i></font>
                <?php } ?>
        </p>
    </div>
    <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />
</form>
