<div class='header ui-widget-header'><?php $clang->eT('Organize question groups/questions');?></div>
<p>
    <?php $clang->eT("To reorder questions/questiongroups just drag the question/group with your mouse to the desired position.");?><br />
    <?php $clang->eT("After you are done please click the bottom 'Save' button to save your changes.");?>
</p>
<div class='movableList'>
    <ol class="organizer">
        <?php
            foreach ($aGroupsAndQuestions as  $aGroupAndQuestions)
            {?>
            <li id='list_g<?php echo $aGroupAndQuestions['gid'];?>'><div class='ui-widget-header'> <?php echo $aGroupAndQuestions['group_name'];?></div>
                <?php if (isset ($aGroupAndQuestions['questions']))
                    {?>
                    <ol>
                        <?php
                            foreach($aGroupAndQuestions['questions'] as $aQuestion)
                            {?>
                            <li id='list_q<?php echo $aQuestion['qid'];?>'><div><b><?php echo $aQuestion['title'];?></b>: <?php echo FlattenText($aQuestion['question'],true);?></div></li>

                            <?php }?>
                    </ol>
                    <?php }?>
            </li>
            <?php
        }?>
    </ol>
</div>
<form id='frmOrganize' action="<?php echo site_url('admin/questiongroup/organize/'.$surveyid);?>" method="post"><p>
        <input type='hidden' id='orgdata' name='orgdata' value='' />
        <button id='btnSave'><?php echo $clang->eT('Save'); ?></button></p>
</form>
