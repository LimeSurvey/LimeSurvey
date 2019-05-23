<?php
    App()->getClientScript()->registerPackage('jquery-nestedSortable');
    App()->getClientScript()->registerScriptFile( App()->getConfig('adminscripts') . 'organize.js', LSYii_ClientScript::POS_BEGIN);
    App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'organize.css');
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(true); ?>'>
        <h3><?php eT('Organize question groups/questions');?></h3>
        <div class='row'>
            <div class='col-sm-8'>
                <p class='alert alert-info'>
                    <span class='fa fa-info-circle'></span>&nbsp;
                    <?php eT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.");?>
                    <?php eT("After you are done, please click the 'Save' button to save your changes.");?>
                </p>
            </div>
            <div class='col-sm-4'>
                <button id='organizer-collapse-all' class='btn btn-default'><span class='fa fa-compress'></span>&nbsp;<?php eT("Collapse all"); ?></button>
                <button id='organizer-expand-all' class='btn btn-default'><span class='fa fa-expand'></span>&nbsp;<?php eT("Expand all"); ?></button>
            </div>
        </div>

        <div class='movableList'>
            <ol class="organizer group-list list-unstyled" data-level='group'>
                <?php
                    foreach ($aGroupsAndQuestions as  $aGroupAndQuestions)
                    {?>
                    <li id='list_g<?php echo $aGroupAndQuestions['gid'];?>' class='panel panel-primary mjs-nestedSortable-expanded' data-level='group'>

                    <div class="panel-heading">
                        <a class='btn btn-default btn-xs disclose'><span title="Click to show/hide children" class="caret"></span></a>
                        &nbsp;
                        <?php echo flattenText($aGroupAndQuestions['group_name'],true);?>
                    </div>
                        <?php if (isset ($aGroupAndQuestions['questions']))
                            {?>
                            <ol class='question-list list-unstyled panel-body' data-level='question'>
                                <?php
                                    foreach($aGroupAndQuestions['questions'] as $aQuestion)
                                    {?>
                                    <li id='list_q<?php echo $aQuestion['qid'];?>' class='well well-sm no-nest' data-level='question'><div>
                                        <b><a href='<?php echo Yii::app()->getController()->createUrl('admin/questions/sa/editquestion/surveyid/'.$surveyid.'/gid/'.$aQuestion['gid'].'/qid/'.$aQuestion['qid']);?>'><?php echo $aQuestion['title'];?></a></b>:
                                         <?php echo ellipsize($aQuestion['question'], 80);?>
                                    </div></li>
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
                <input type='hidden' id='close-after-save' name='close-after-save' value='' />
                <button class='hidden' type="submit" id='btnSave'>
                    <?php echo eT('Save'); ?>
                </button>
            </p>
        </form>
        <!-- If user do a change in the list, and try to leave without saving, he'll be warn with this message -->
        <input type="hidden" value="off" id="didChange" data-message="<?php eT("You didn't save your changes!"); ?>" />
</div>
