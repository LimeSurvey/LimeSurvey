<?php
/**
 * Question group bar
 * Also used to Edit question
 */

?>

<!-- nquestiongroupbar -->
<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid'>

        <?php if(isset($questiongroupbar['buttonspreview']) || isset($questiongroupbar['buttons']['view']) ):?>
            <div class="col-md-4">
            <!-- test/execute survey -->
            <?php if (count($languagelist) > 1): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                        <span class="icon-do" ></span>
                        <?php if($oSurvey->active=='N'):?>
                            <?php eT('Preview survey');?>
                        <?php else: ?>
                            <?php eT('Execute survey');?>
                        <?php endif;?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" style="min-width : 252px;">
                        <?php foreach ($languagelist as $tmp_lang): ?>
                            <li>
                                <a target='_blank' href='<?php echo $this->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                                    <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <a class="btn btn-default  btntooltip" href="<?php echo $this->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$oSurvey->language)); ?>" role="button"  accesskey='d' target='_blank'>
                    <span class="icon-do" ></span>
                    <?php if($oSurvey->active=='N'):?>
                        <?php eT('Preview survey');?>
                    <?php else: ?>
                        <?php eT('Execute survey');?>
                    <?php endif;?>
                </a>
            <?php endif;?>

            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                <?php if (count($languagelist) > 1): ?>

                    <!-- Preview multilangue -->
                    <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span class="icon-do"></span>
                        <?php eT("Preview question group"); ?> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" style="min-width : 252px;">
                          <?php foreach ($languagelist as $tmp_lang): ?>
                              <li>
                                  <a target="_blank" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>" >
                                      <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                  </a>
                              </li>
                          <?php endforeach; ?>
                      </ul>
                    </div>
                <?php else:?>

                    <!-- Preview simple langue -->
                    <a class="btn btn-default" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" role="button" target="_blank">
                        <span class="icon-do"></span>
                        <?php eT("Preview question group");?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif;?>

        <!-- Left Buttons -->
        <div class="col-md-8 text-right no-padding  pull-right ">


            <?php if(isset($questiongroupbar['buttons']['view'])):?>
                <!-- Buttons -->


                <!-- Edit button -->
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                    <a class="btn btn-default pjax" href="<?php echo $this->createUrl('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid); ?>" role="button">
                        <span class="icon-edit"></span>
                        <?php eT("Edit current question group");?>
                    </a>
                <?php endif; ?>

                <!-- Check survey logic -->
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
                    <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
                        <span class="icon-expressionmanagercheck"></span>
                        <?php eT("Check survey logic for current question group"); ?>
                    </a>
                <?php endif; ?>

                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')):?>

                    <!-- Delete -->
                    <?php if( ($sumcount4 == 0 && $activated != "Y") || $activated != "Y" ):?>

                        <!-- has question -->
                        <?php if(is_null($condarray)):?>
                            <!-- can delete group and question -->
                            <button
                                class="btn btn-default"
                                data-toggle="modal"
                                data-target="#confirmation-modal"
                                data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("admin/questiongroups/sa/delete/", ["surveyid" => $surveyid, "gid"=>$gid])); ?> })'
                                data-message="<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>"
                                >
                                <span class="fa fa-trash"></span>
                                <?php eT("Delete current question group"); ?>
                            </button>
                        <?php else: ?>
                            <!-- there is at least one question having a condition on its content -->
                            <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content"); ?>" >
                                <span class="fa fa-trash"></span>
                                <?php eT("Delete current question group"); ?>
                            </a>
                        <?php endif; ?>
                    <?php else:?>
                        <!-- Activated -->
                        <button type="button" class="btn btn-default btntooltip" disabled data-toggle="tooltip" data-placement="bottom" title="<?php eT("You can't delete this question group because the survey is currently active."); ?>" >
                            <span class="fa fa-trash"></span>
                            <?php eT("Delete current question group"); ?>
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')):?>

                    <!-- Export -->
                    <a class="btn btn-default " href="<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>" role="button">

                        <span class="icon-export"></span>
                        <?php eT("Export this question group"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-sm-8">
            <!-- Previews while editing a question -->
            <?php if(isset($questiongroupbar['savebutton']['form'])&&isset($qid)):?>
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                    <?php if (count($languagelist) > 1): ?>

                        <!-- test/execute survey -->
                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                            <span class="icon-do" ></span>
                            <?php if($oSurvey->active=='N'):?>
                                <?php eT('Preview survey');?>
                            <?php else: ?>
                                <?php eT('Execute survey');?>
                            <?php endif;?>
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" style="min-width : 252px;">
                            <?php foreach ($languagelist as $tmp_lang): ?>
                                <li>
                                    <a target='_blank' href='<?php echo $this->createUrl("survey/index",array('sid'=>$surveyid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                                        <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                          </ul>
                        </div>


                        <!-- Preview multilangue -->

                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="icon-do"></span>
                            <?php eT("Preview question group"); ?> <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" style="min-width : 252px;">
                              <?php foreach ($languagelist as $tmp_lang): ?>
                                  <li>
                                      <a target="_blank" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/{$surveyid}/gid/{$gid}/lang/" . $tmp_lang); ?>" >
                                          <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                      </a>
                                  </li>
                              <?php endforeach; ?>
                          </ul>
                        </div>

                        <!-- preview question -->
                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="icon-do"></span>
                            <?php eT("Preview question"); ?> <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu" style="min-width : 252px;">
                              <?php foreach ($languagelist as $tmp_lang): ?>
                                  <li>
                                      <a target="_blank" href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid . "/lang/" . $tmp_lang); ?>' >
                                          <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                      </a>
                                  </li>
                              <?php endforeach; ?>
                          </ul>
                        </div>
                    <?php else:?>
                        <!-- Preview/Execute survey -->
                        <a class="btn btn-default  btntooltip selector__topbar--previewSurvey" href="<?php echo $this->createUrl("survey/index/sid/$surveyid/newtest/Y/lang/$oSurvey->language"); ?>" role="button"  accesskey='d' target='_blank'>
                            <span class="icon-do" ></span>
                            <?php if($oSurvey->active=='N'):?>
                                <?php eT('Preview survey');?>
                            <?php else: ?>
                                <?php eT('Execute survey');?>
                            <?php endif;?>
                        </a>

                        <!-- preview question -->
                        <a class="btn btn-default" href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid); ?>' role="button" target="_blank">
                            <span class="icon-do"></span>
                            <?php eT("Preview");?>
                        </a>

                        <!-- Preview simple langue -->
                        <a class="btn btn-default" href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/$surveyid/gid/$gid/"); ?>" role="button" target="_blank">
                            <span class="icon-do"></span>
                            <?php eT("Preview question group");?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

            <?php endif;?>

        </div>

        <!-- Right Buttons -->
        <div class="col-sm-4 pull-right text-right">

            <!-- Save buttons -->
            <?php if(isset($questiongroupbar['savebutton']['form'])):?>
                <?php if(!isset($copying) || !$copying): ?>
                <a class="btn btn-success" href="#" role="button" id="save-button">
                    <i class="fa fa-floppy-o"></i>
                    <?php eT("Save");?>
                </a>
               
                <?php endif; ?>
            <?php endif; ?>
            

            <!-- Save and close -->
            <?php if(isset($questiongroupbar['saveandclosebutton'])):?>
                <a id="save-and-close-button" class="btn btn-default" role="button">
                    <i class="fa fa-check-square"></i>
                    <?php eT("Save and close");?>
                </a>
            <?php endif; ?>
            
 
            <!-- Close -->
            <?php if(isset($questiongroupbar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $questiongroupbar['closebutton']['url']; ?>" role="button">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <!-- return -->
            <?php if(isset($questiongroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $questiongroupbar['returnbutton']['url']; ?>" role="button">
                    <span class="fa fa-step-backward"></span>
                    <?php echo $questiongroupbar['returnbutton']['text'];?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
<?php /*


                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create')): ?>
                    <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php if ($surveyIsActive) { echo eT("You can't add questions while the survey is active."); } ?>" >
                       <a class="btn btn-default <?php if ($surveyIsActive) { echo "disabled"; } ?>" href="<?php echo $this->createUrl('admin/questions/sa/newquestion/surveyid/'.$surveyid.'/gid/'.$gid); ?>" role="button">
                           <span class="icon-add"></span>
                           <?php eT("Add new question to group");?>
                       </a>
                   </span>
                <?php endif; ?>
*/ ?>
