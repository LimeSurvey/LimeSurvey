<script type='text/javascript'>
    var languagecount=<?php echo count($anslangs); ?>;
    var newansweroption_text='<?php $clang->eT('New answer option','js'); ?>';
    var strcode='<?php $clang->eT('Code','js'); ?>';
    var strlabel='<?php $clang->eT('Label','js'); ?>';
    var strCantDeleteLastAnswer='<?php $clang->eT('You cannot delete the last subquestion.','js'); ?>';
    var lsbrowsertitle='<?php $clang->eT('Label set browser','js'); ?>';
    var quickaddtitle='<?php $clang->eT('Quick-add subquestions','js'); ?>';
    var duplicateanswercode='<?php $clang->eT('Error: You are trying to use duplicate subquestion codes.','js'); ?>';
    var langs='<?php echo implode(';',$anslangs); ?>';
    var ci_path='<?php echo Yii::app()->getConfig('imageurl'); ?>';
</script>
<?php echo PrepareEditorScript(); ?>
<div class='header ui-widget-header'>
        <?php $clang->eT("Edit subquestions"); ?>
</div>
<form id='editsubquestionsform' name='editsubquestionsform' method='post' action='<?php echo $this->createUrl("admin/database"); ?>' onsubmit="return codeCheck('code_',<?php echo $maxsortorder; ?>,'<?php $clang->eT("Error: You are trying to use duplicate answer codes.",'js'); ?>','<?php $clang->eT("Error: 'other' is a reserved keyword.",'js'); ?>');">
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
        <?php if ($anslang==Survey::model()->findByPk($surveyid)->language) { ?> (<?php echo $clang->gT("Base Language"); ?>) <?php } ?></a>
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
                <table class='answertable' id='answertable_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' align='center'>
                <thead>
                <tr><th>&nbsp;</th>
                <th align='right'><?php $clang->eT("Code"); ?></th>
                <th align='center'><?php $clang->eT("Subquestion"); ?></th>
                <?php if ($activated != 'Y' && $first)
                { ?>
                    <th align='center'><?php $clang->eT("Action"); ?></th>
                <?php } ?>
                </tr></thead>
                <tbody align='center'>
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
                     ><td align='right'>

                    <?php if ($activated == 'Y' ) // if activated
                    { ?>
                        &nbsp;</td><td><input type='hidden' name='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" maxlength='5' size='5'
                         /><?php echo $row->title; ?>
                    <?php }
                    elseif ($activated != 'Y' && $first) // If survey is decactivated
                    { ?>
                        <img class='handle' src='<?php echo Yii::app()->getConfig('imageurl')?>/handle.png' /></td><td><input type='hidden' class='oldcode' id='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" /><input type='text' id='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' class='code' name='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->title; ?>" maxlength='5' size='5'
                         onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')"
                         />

                    <?php }
                    else
                    { ?>
                        </td><td><?php echo $row->title; ?>

                    <?php } ?>

                    </td><td>
                    <input type='text' size='100' id='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->question; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}" />
                    <?php echo  getEditor("editanswer","answer_".$row->language."_".$row->qid."_{$row->scale_id}", "[".$clang->gT("Subquestion:", "js")."](".$row->language.")",$surveyid,$gid,$qid,'editanswer'); ?>
                    </td>
                    <td>


                    <?php if ($activated != 'Y' && $first)
                    { ?>
                        <img src='<?php echo Yii::app()->getConfig('imageurl')?>/addanswer.png' class='btnaddanswer' />
                        <img src='<?php echo Yii::app()->getConfig('imageurl')?>/deleteanswer.png' class='btndelanswer' />
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
                <button class='btnlsbrowser' id='btnlsbrowser_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php $clang->eT('Predefined label sets...'); ?></button>
                <button class='btnquickadd' id='btnquickadd_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php $clang->eT('Quick add...'); ?></button>
                <?php if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1 || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1){ ?>
                    <button class='bthsaveaslabel' id='bthsaveaslabel_<?php echo $scale_id; ?>' <?php echo $disabled; ?> type='button'><?php $clang->eT('Save as label set'); ?></button>
                <?php } ?>

            <?php }

            $first=false; ?>
            </div>
        <?php } ?>
<div id='labelsetbrowser' style='display:none;'>
    <div style='float:left; width:260px;'>
        <label for='labelsets'><?php $clang->eT('Available label sets:'); ?></label>
        <br /><select id='labelsets' size='10' style='width:250px;'><option>&nbsp;</option></select>
        <br /><button id='btnlsreplace' type='button'><?php $clang->eT('Replace'); ?></button>
        <button id='btnlsinsert' type='button'><?php $clang->eT('Add'); ?></button>
        <button id='btncancel' type='button'><?php $clang->eT('Cancel'); ?></button>
    </div>
    <div id='labelsetpreview' style='float:right;width:500px;'>
    </div>
</div>
<div id='quickadd' style='display:none;'>
    <div style='float:left;'>
        <label for='quickadd'><?php $clang->eT('Enter your subquestions:'); ?></label>
        <br /><textarea id='quickaddarea' class='tipme' title='<?php $clang->eT('Enter one subquestion per line. You can provide a code by separating code and subquestion text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or space.'); ?>' cols='100' rows='30' style='width:570px;'></textarea>
        <br /><button id='btnqareplace' type='button'><?php $clang->eT('Replace'); ?></button>
        <button id='btnqainsert' type='button'><?php $clang->eT('Add'); ?></button>
        <button id='btnqacancel' type='button'><?php $clang->eT('Cancel'); ?></button>
    </div>
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
</form>