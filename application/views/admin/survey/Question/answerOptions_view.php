<?php echo PrepareEditorScript(true, $this); ?>
<div class='header ui-widget-header'>
        <?php $clang->eT("Edit answer options"); ?>
</div>
<form id='editanswersform' name='editanswersform' method='post' action='<?php echo $this->createUrl('admin/database'); ?>'>
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
    var strcode='<?php $clang->eT('Code','js'); ?>';
    var strlabel='<?php $clang->eT('Label','js'); ?>';
    var strCantDeleteLastAnswer='<?php $clang->eT('You cannot delete the last answer option.','js'); ?>';
    var lsbrowsertitle='<?php $clang->eT('Label set browser','js'); ?>';
    var quickaddtitle='<?php $clang->eT('Quick-add answers','js'); ?>';
    var sAssessmentValue='<?php $clang->eT('Assessment value','js'); ?>';
    var duplicateanswercode='<?php $clang->eT('Error: You are trying to use duplicate answer codes.','js'); ?>';
    var langs='<?php echo implode(';',$anslangs); ?>';
    var ci_path="<?php echo Yii::app()->getConfig('imageurl'); ?>";
</script>
<div id='tabs'>
<ul>
    <?php foreach ($anslangs as $anslang)
    { ?>
        <li><a href='#tabpage_<?php echo $anslang; ?>'><?php echo getLanguageNameFromCode($anslang, false); ?>
        <?php if ($anslang==Survey::model()->findByPk($surveyid)->language) { ?> (<?php $clang->eT("Base Language"); ?>) <?php } ?></a>
        </li>
    <?php } ?>
</ul>

    <?php foreach ($anslangs as $anslang)
        { ?>
            <div id='tabpage_<?php echo $anslang; ?>' class='tab-page'>

            <?php for ($scale_id = 0; $scale_id < $scalecount; $scale_id++)
            {
                $position=0;
                if ($scalecount>1)
                { ?>
                    <div class='header ui-widget-header' style='margin-top:5px;'><?php echo sprintf($clang->gT("Answer scale %s"),$scale_id+1); ?></div>
                <?php } ?>


                <table class='answertable' id='answers_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' align='center' >
                <thead>
                <tr>
                <th align='right'>&nbsp;</th>
                <th align='center'><?php $clang->eT("Code"); ?></th>
                <?php if ($assessmentvisible)
                { ?>
                    <th align='center'><?php $clang->eT("Assessment value"); ?>
                <?php }
                else
                { ?>
                    <th style='display:none;'>&nbsp;
                <?php } ?>

                </th>
                <th align='center'><?php $clang->eT("Answer option"); ?></th>
                <th align='center'><?php $clang->eT("Actions"); ?></th>
                </tr></thead>
                <tbody align='center'>
                <?php $alternate=true;

                $query = "SELECT * FROM {{answers}} WHERE qid='{$qid}' AND language='{$anslang}' and scale_id=$scale_id ORDER BY sortorder, code";
                $result = db_execute_assoc($query);
                $anscount = $result->count();

                foreach ($result->readAll() as $row)
                {
                    $row['code'] = htmlspecialchars($row['code']);
                    $row['answer']=htmlspecialchars($row['answer']);
                    ?>
                    <tr class='row_<?php echo $position; ?>
                    <?php if ($alternate==true)
                    { ?>
                        highlight
                    <?php } ?>
                    <?php $alternate=!$alternate; ?>
                     '><td align='right'>

                    <?php if ($first)
                    { ?>
                        <img class='handle' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/handle.png' /></td><td><input type='hidden' class='oldcode' id='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['code']; ?>" /><input type='text' class='code' id='code_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['code']; ?>" maxlength='5' size='5'
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
                         style='display:none;'><input type='hidden' class='assessment' id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>' value="<?php echo $row['assessment_value']; ?>" maxlength='5' size='5'
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
                    <input type='text' class='answer' id='answer_<?php echo $row['language']; ?>_<?php echo $row['sortorder']; ?>_<?php echo $scale_id; ?>' name='answer_<?php echo $row['language']; ?>_<?php echo $row['sortorder']; ?>_<?php echo $scale_id; ?>' size='100' value="<?php echo $row['answer']; ?>" />
                    <?php echo  getEditor("editanswer","answer_".$row['language']."_{$row['sortorder']}_{$scale_id}", "[".$clang->gT("Answer:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer'); ?>


                    </td><td><img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/addanswer.png' class='btnaddanswer' />
                    <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/deleteanswer.png' class='btndelanswer' />

                    </td></tr>
                    <?php $position++;
                } ?>
                </table><br />
                <?php if ($first)
                { ?>
                    <input type='hidden' id='answercount_<?php echo $scale_id; ?>' name='answercount_<?php echo $scale_id; ?>' value='<?php echo $anscount; ?>' />
                <?php } ?>
                <button id='btnlsbrowser_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btnlsbrowser' type='button'><?php $clang->eT('Predefined label sets...'); ?></button>
                <button id='btnquickadd_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btnquickadd' type='button'><?php $clang->eT('Quick add...'); ?></button>

                <?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1){ ?>
                    <button class='bthsaveaslabel' id='bthsaveaslabel_<?php echo $scale_id; ?>' type='button'><?php $clang->eT('Save as label set'); ?></button>

                    <?php }
            }

            $position=sprintf("%05d", $position);

            $first=false; ?>
            </div>
        <?php } ?>
      <div id='labelsetbrowser' style='display:none;'><div style='float:left;width:260px;'>
                          <label for='labelsets'><?php $clang->eT('Available label sets:'); ?></label>
                          <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
                          <br /><button id='btnlsreplace' type='button'><?php $clang->eT('Replace'); ?></button>
                          <button id='btnlsinsert' type='button'><?php $clang->eT('Add'); ?></button>
                          <button id='btncancel' type='button'><?php $clang->eT('Cancel'); ?></button></div>

                       <div id='labelsetpreview' style='float:right;width:500px;'></div></div>
        <div id='quickadd' style='display:none;'><div style='float:left;'>
                          <label for='quickadd'><?php $clang->eT('Enter your answers:'); ?></label>
                          <br /><textarea id='quickaddarea' class='tipme' title='<?php $clang->eT('Enter one answer per line. You can provide a code by separating code and answer text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.'); ?>' cols='100' rows='30' style='width:570px;'></textarea>
                          <br /><button id='btnqareplace' type='button'><?php $clang->eT('Replace'); ?></button>
                          <button id='btnqainsert' type='button'><?php $clang->eT('Add'); ?></button>
                          <button id='btnqacancel' type='button'><?php $clang->eT('Cancel'); ?></button></div>
                       </div>

        <p><input type='submit' id='saveallbtn_<?php echo $anslang; ?>' name='method' value='<?php $clang->eT("Save changes"); ?>' />
        </div></form>
