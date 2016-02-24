<?php
    App()->getClientScript()->registerPackage('jquery-nestedSortable');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'organize.js');
    App()->getClientScript()->registerCssFile(Yii::app()->getConfig('styleurl') . 'organize.css');
?>

<div class="side-body" id="edit-survey-text-element">
    <div class="row">
        <h3><?php eT('Organize question groups/questions');?></h3>
        <p>
            <?php eT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.");?><br />
            <?php eT("After you are done please click the bottom 'Save' button to save your changes.");?>
        </p>

        <div class='movableList'>
            <ol class="organizer group-list list-unstyled" data-level='group'>
                <?php
                    foreach ($aGroupsAndQuestions as  $aGroupAndQuestions)
                    {?>
                    <li id='list_g<?php echo $aGroupAndQuestions['gid'];?>' class='group-item' data-level='group'>
                        <div class="h4"> <?php echo flattenText($aGroupAndQuestions['group_name'],true);?></div>
                        <?php if (isset ($aGroupAndQuestions['questions']))
                            {?>
                            <ol class='question-list list-unstyled' data-level='question'>
                                <?php
                                    foreach($aGroupAndQuestions['questions'] as $aQuestion)
                                    {?>
                                    <li id='list_q<?php echo $aQuestion['qid'];?>' class='question-item' data-level='question'><div>
                                        <a class="btn hide-button" aria-hidden="true"><span class="caret"></span></a>
                                        <b><a href='<?php echo Yii::app()->getController()->createUrl('admin/questions/sa/editquestion/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']);?>'><?php echo $aQuestion['title'];?></a></b>:
                                         <?php echo $aQuestion['question'];?>
                                    </div></li>
                                    <?php }?>
                            </ol>
                            <?php }?>
                    </li>
                    <?php
                }?>
            </ol>
        </div>

        <?php echo CHtml::form(array("admin/survey/sa/organize/surveyid/{$surveyid}"), 'post', array('id'=>'frmOrganize', 'onsubmit'=>'setFormSubmitting();' )); ?>
            <p>
                <input type='hidden' id='orgdata' name='orgdata' value='' />
                <input type='hidden' id='close-after-save' name='close-after-save' value='' />
                <button class='hidden' type="submit" id='btnSave' onclick='setFormSubmitting();'>
                    <?php echo eT('Save'); ?>
                </button>
            </p>
        </form>
        <!-- If user do a change in the list, and try to leave without saving, he'll be warn with this message -->
        <input type="hidden" value="off" id="didChange" data-message="<?php eT("You didn't save your changes!"); ?>" />
    </div>
</div>
