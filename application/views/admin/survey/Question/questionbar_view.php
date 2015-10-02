<?php
$aReplacementData=array();
?>
<div class='menubar surveybar' id="questionbarid">
    <div class='row container-fluid'>
        <?php if(isset($questionbar['buttons']['view'])):?>
        <div class="col-md-12">
        
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
                <?php if (count($languagelist) > 1): ?>
                    <!-- Single button -->
                    <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
                        <?php eT("Preview"); ?> <span class="caret"></span>
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
                    <a class="btn btn-default" href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $surveyid . "/gid/" . $gid . "/qid/" . $qid); ?>' role="button" target="_blank">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
                        <?php eT("Preview ");?>
                    </a>
                <?php endif; ?>                    
            <?php else: ?>
                <a class="btn disabled" href="#" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/preview.png" />
                    <?php eT("Preview ");?>
                </a>                
            <?php endif; ?>        


            <!-- Edit button -->            
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')): ?>
                <a class="btn btn-default" href='<?php echo $this->createUrl("admin/questions/sa/editquestion/surveyid/".$surveyid."/gid/".$gid."/qid/".$qid); ?>' role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/edit.png" />
                    <?php eT("Edit");?>
                </a>            
            <?php endif; ?>            
    
    
            <!-- Check logic -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')): ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/{$surveyid}/gid/{$gid}/qid/{$qid}/"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/quality_assurance.png" />
                    <?php eT("Check logic"); ?>
                </a>
            <?php endif; ?>
    
    
            <!-- Delete -->
            <?php if( $activated != "Y" && Permission::model()->hasSurveyPermission($surveyid,'surveycontent','delete' )):?>
                <a class="btn btn-default" onclick="if (confirm('<?php eT("Deleting  will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl("admin/questions/sa/delete/surveyid/$surveyid/gid/$gid/qid/$qid")); ?>}">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
                    <?php eT("Delete"); ?>
                </a>
            <?php else:?>
                <a href='<?php echo $this->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>'  
                    class="btn btn-default" 
                    onclick="alert('<?php eT("You can't delete  because the survey is currently active.","js"); ?>')">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
                    <?php eT("Delete current question group"); ?>
                </a>                
            <?php endif; ?>
            
            
            <!-- export -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','export')):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/question/surveyid/$surveyid/gid/$gid/qid/$qid");?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/dumpquestion.png" />
                    <?php eT("Export "); ?>
                </a>        
            <?php endif; ?>
            
            <!-- copy -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','create')):?>
                <?php if(($activated != "Y")):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/questions/sa/copyquestion/surveyid/$surveyid/gid/$gid/qid/$qid");?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/copy.png" />
                        <?php eT("Copy"); ?>
                    </a>                    
                <?php else:?>
                    <a class="btn disabled" href="#" role="button" onclick="alert('<?php eT("You can't copy a question if the survey is active.","js"); ?>'>
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/copy.png" />
                        <?php eT("Copy"); ?>
                    </a>                    
                <?php endif;?>
            <?php else:?>
                    <a class="btn disabled" href="#" role="button" onclick="alert('<?php eT("You don't have necessary permission","js"); ?>'>
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/copy.png" />
                        <?php eT("Copy"); ?>
                    </a>                
            <?php endif;?>                

            <!-- conditions -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','update')):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/conditions.png" />
                        <?php eT("Set conditions "); ?>
                    </a>                
            <?php else:?>
                    <a class="btn disabled" href="#" role="button" onclick="alert('<?php eT("You don't have necessary permission","js"); ?>')">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/conditions.png" />
                        <?php eT("Set conditions "); ?>
                    </a>                    
            <?php endif;?>
                

            <!-- subquestions -->
            
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read')):?>
                <?php if($qtypes[$qrrow['type']]['subquestions'] >0):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/questions/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/subquestions.png" />
                        <?php eT("Edit subquestions "); ?>
                    </a>
                <?php endif;?>
            <?php endif;?>                                                                                        
            
            <!-- Answer Options -->
            <?php if( Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] > 0 ):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/questions/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/answers.png" />
                        <?php eT("Edit answer options "); ?>
                    </a>                
            <?php endif;?>
 

            <!-- Default Values -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl('admin/questions/sa/editdefaultvalues/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid); ?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/defaultanswers.png" />
                        <?php eT("Edit default answers"); ?>
                    </a>                                
            <?php endif;?>
    </div>            
<?php endif;?>


<?php if(isset($questionbar['buttons']['conditions'])):?>
<div class="col-md-7">
    <a class="btn btn-default <?php if(isset($questionbar['buttons']['condition']['conditions'])){echo 'active';}?>" href="<?php echo $this->createUrl("/admin/conditions/sa/index/subaction/conditions/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/summary.png" />
        <?php eT("Show conditions for this question");?>
    </a>

    <a class="btn btn-default <?php if($questionbar['buttons']['condition']['edit']){echo 'active';}?>" href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/editconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/conditions_add.png" />
        <?php eT("Add and edit conditions");?>
    </a>

    <a class="btn btn-default <?php if(isset($questionbar['buttons']['condition']['copyconditionsform'])){echo 'active';}?>" href="<?php echo $this->createUrl("admin/conditions/sa/index/subaction/copyconditionsform/surveyid/$surveyid/gid/$gid/qid/$qid"); ?>" role="button">
        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/conditions_copy.png" />
        <?php eT("Copy conditions");?>
    </a>
</div>
<?php endif;?>
        
        <div class="col-md-5 text-right form-inline">
                <?php if(isset($questionbar['savebutton']['form'])):?>
                    <a class="btn btn-success" href="#" role="button">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        <?php eT("Save");?>
                    </a>
    
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/282267{$surveyid}"); ?>" role="button">
                        <span class="glyphicon glyphicon-saved" aria-hidden="true"></span>
                        <?php eT("Save and close");?>
                    </a>
                <?php endif;?>


                <?php if(isset($questionbar['buttons']['conditions'])):?>
                
                    <div class="form-group">
                        <label for='questionNav'><?php eT("Move to question");?>:</label>
                        <select id='questionNav' class="form-control"  onchange="window.open(this.options[this.selectedIndex].value,'_top')"><?php echo $quesitonNavOptions;?></select>
                    </div>

                <?php endif;?>
                
                <?php if(isset($questionbar['closebutton']['url'])):?>
                    <!-- $this->createUrl("admin/survey/sa/listquestiongroups/surveyid/{$surveyid}"); ?>-->
                    <a class="btn btn-danger" href="<?php echo $this->createUrl($questionbar['closebutton']['url']); ?>" role="button">
                        <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                        <?php eT("Close");?>
                    </a>
                <?php endif;?>
                
                
                <?php if(isset($questionbar['returnbutton']['url'])):?>
                    <a class="btn btn-default" href="<?php echo $questionbar['returnbutton']['url']; ?>" role="button">
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>
                        <?php echo $questionbar['returnbutton']['text'];?>
                    </a>
                <?php endif;?>
        </div>
    </div>
</div>
