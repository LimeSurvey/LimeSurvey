<?php
    App()->getClientScript()->registerPackage('jquery-nestedSortable');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'organize.js');
?>
<div class='header ui-widget-header'><?php $clang->eT('Organize question groups/questions');?></div>
<p>
    <?php $clang->eT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.");?><br />
    <?php $clang->eT("After you are done please click the bottom 'Save' button to save your changes.");?>
</p>
<div class='movableList'>
    <ol class="organizer group-list" data-level='group'>
        <?php
            foreach ($aGroupsAndQuestions as  $aGroupAndQuestions)
            {?>
            <li id='list_g<?php echo $aGroupAndQuestions['gid'];?>' class='group-item' data-level='group'><div class='ui-widget-header'> <?php echo flattenText($aGroupAndQuestions['group_name'],true);?></div>
                <?php if (isset ($aGroupAndQuestions['questions']))
                    {?>
                    <ol class='question-list' data-level='question'>
                        <?php
                            foreach($aGroupAndQuestions['questions'] as $aQuestion)
                            {?>
                            <li id='list_q<?php echo $aQuestion['qid'];?>' class='question-item' data-level='question'><div><b><a href='<?php echo Yii::app()->getController()->createUrl('admin/questions/sa/editquestion/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']);?>'><?php echo $aQuestion['title'];?></a></b>: <?php echo flattenText($aQuestion['question'],true);?></div></li>

                            <?php }?>
                    </ol>
                    <?php }?>
            </li>
            <?php
        }?>
    </ol>
</div>
<?php echo CHtml::form(array("admin/survey/sa/organize/surveyid/{$surveyid}"), 'post', array('id'=>'frmOrganize')); ?>
    <p>
        <input type='hidden' id='orgdata' name='orgdata' value='' />
        <button id='btnSave'><?php echo $clang->eT('Save'); ?></button>
    </p>
</form>
