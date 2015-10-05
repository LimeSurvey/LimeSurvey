<?php
/**
 * Question group bar
 * Also used for Edit question
 */
?>

<!-- nquestiongroupbar -->
<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid'>
        
        <!-- Left Buttons -->
        <div class="col-md-12">
            <?php if(isset($questiongroupbar['buttons']['view'])):?>
                <!-- Buttons -->
                                                                             
               <a class="btn btn-default" href="<?php echo $this->createUrl('admin/questions/sa/newquestion/surveyid/'.$surveyid.'/gid/'.$gid); ?>" role="button">
                   <img src="<?php echo $sImageURL; ?>add.png" />
                   <?php eT("Add new question to group");?>
               </a>            
                
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                    <?php if (count($languagelist) > 1): ?>
                        
                        <!-- Preview multilangue -->
                        <div class="btn-group">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <img src="<?php echo $sImageURL; ?>preview.png" />
                            <?php eT("Preview this question group"); ?> <span class="caret"></span>
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
                            <img src="<?php echo $sImageURL; ?>preview.png" />
                            <?php eT("Preview this question group");?>
                        </a>
                    <?php endif; ?>                    
                <?php endif; ?>        
                    
                <!-- Edit button -->            
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid); ?>" role="button">
                        <img src="<?php echo $sImageURL; ?>edit.png" />
                        <?php eT("Edit current question group");?>
                    </a>            
                <?php endif; ?>            
        
                <!-- Check survey logic -->
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/"); ?>" role="button">
                        <img src="<?php echo $sImageURL; ?>quality_assurance.png" />
                        <?php eT("Check survey logic for current question group"); ?>
                    </a>
                <?php endif; ?>
        
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete')):?>
                    
                    <!-- Delete -->
                    <?php if( ($sumcount4 == 0 && $activated != "Y") || $activated != "Y" ):?>
                        
                        <!-- has question -->
                        <?php if(is_null($condarray)):?>
                            
                            <!-- can delete group and question -->
                            <a class="btn btn-default" onclick="if (confirm('<?php eT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js"); ?>')) { window.open('<?php echo $this->createUrl("admin/questiongroups/sa/delete/surveyid/$surveyid/gid/$gid"); ?>','_top'); }" role="button">
                                <img src="<?php echo $sImageURL; ?>delete.png" />
                                <?php eT("Delete current question group"); ?>
                            </a>
                        <?php else: ?>
                            
                            <!-- there is at least one question having a condition on its content -->
                            <a href='<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid/gid/$gid"); ?>'  class="btn btn-default" onclick="alert('<?php eT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>'); return false;">
                                <img src="<?php echo $sImageURL; ?>delete.png" />
                                <?php eT("Delete current question group"); ?>
                            </a>
                        <?php endif; ?>
                    <?php else:?>
    
                        <!-- Activated -->
                        <span class="btntooltip" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Impossible to delete this group because there is at least one question having a condition on its content","js"); ?>" >
                            <button type="button" class="btn btn-default btntooltip" disabled="disabled">
                                <img src="<?php echo $sImageURL; ?>delete.png" />
                                <?php eT("Delete current question group"); ?>
                            </button>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')):?>
                    
                    <!-- Export -->
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/group/surveyid/$surveyid/gid/$gid");?>" role="button">
                        <img src="<?php echo $sImageURL; ?>dumpgroup.png" />
                        <?php eT("Export this question group"); ?>
                    </a>        
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Right Buttons -->
        <div class="col-md-4 col-md-offset-8 text-right">
            
            <?php if(isset($questiongroupbar['savebutton']['form'])):?>
                <!-- Save buttons -->
                <a class="btn btn-success" href="#" role="button" id="save-button">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php eT("Save");?>
                </a>
                
                <?php if(isset($questiongroupbar['saveandclosebutton'])):?>
                    
                    <!-- Save and close -->
                    <a id="save-and-close-button" class="btn btn-default" role="button">
                        <span class="glyphicon glyphicon-saved" aria-hidden="true"></span>
                        <?php eT("Save and close");?>
                    </a>
                <?php endif; ?>
            <?php endif;?>
            
            <?php if(isset($questiongroupbar['closebutton']['url'])):?>
                
                <!-- Close -->
                <a class="btn btn-danger" href="<?php echo $this->createUrl($questiongroupbar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
            
            <?php if(isset($questiongroupbar['returnbutton']['url'])):?>
                
                <!-- return -->
                <a class="btn btn-default" href="<?php echo $questiongroupbar['returnbutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
                    <?php echo $questiongroupbar['returnbutton']['text'];?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
