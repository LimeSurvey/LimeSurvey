<?php $this->renderPartial("./survey/Question/question_subviews/_ajax_variables", $ajaxDatas); ?>
<?php $this->renderPartial("./survey/Question/newQuestion_subviews/_ajax_variables", $ajaxDatas); ?>

<script type='text/javascript'><?php echo $qTypeOutput; ?></script>
<?php PrepareEditorScript(true, $this); ?>


<div class="side-body" id="edit-question-body">
    
    <!-- Page Title-->
    <h3>
        <?php 
                if ($adding) 
                {
                    eT("Add a new question");
                } 
                elseif ($copying) 
                { 
                    eT("Copy question");
                } 
                else 
                {
                    eT("Edit question");
                } 
        ?>		
	</h3>
	
	<div class="row">
	    <!-- Form for the whole page-->
	    <?php echo CHtml::form(array("admin/database/index"), 'post',array('class'=>'form30 form-horizontal','id'=>'frmeditquestion','name'=>'frmeditquestion')); ?>
		<?php if(!$adding):?>
		    
		<!-- The tabs & tab-fanes -->
		<div class="col-lg-8 content-right">
            <?php $this->renderPartial('./survey/Question/question_subviews/_tabs',array('eqrow'=>$eqrow,'addlanguages'=>$addlanguages, 'surveyid'=>$surveyid, 'gid'=>NULL, 'qid'=>NULL, 'adding'=>$adding, 'aqresult'=>$aqresult, 'action'=>$action )); ?>
        </div>

        <!-- The Accordion -->			        
        <div class="col-lg-4">
            <?php
                // TODO : find why the $groups can't be generated from controller 
            ?>                
            <div id='questionbottom'>
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <div class="panel panel-default" id="questionTypeContainer">
                        <div class="panel-heading" role="tab" id="headingOne">
                          <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                             <?php eT("General Option");?>
                            </a>
                          </h4>
                        </div>
                        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                            <div class="panel-body">
                                <div>
                                    <div  class="form-group">
                                        <label class="col-sm-4 control-label" for="question_type_button">
                                            <?php eT("Question Type:"); ?>
                                        </label>
                                        <?php if($selectormodeclass!="none"): ?>
                                            <?php
                                                foreach (getQuestionTypeList($eqrow['type'], 'array') as $key=> $questionType)
                                                {
                                                    if (!isset($groups[$questionType['group']]))
                                                    {
                                                        $groups[$questionType['group']] = array();
                                                    }
                                                    $groups[$questionType['group']][$key] = $questionType['description'];
                                                }
                                            ?>
                                            
                                            <input type="hidden" id="question_type" name="type" value="<?php echo $eqrow['type']; ?>" />
                                            
                                            <div class="col-sm-8 btn-group" id="question_type_button" style="z-index: 1000">
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="z-index: 1000">
                                                    <?php foreach($groups as $name => $group):?>
                                                        <?php foreach($group as $type => $option):?>
                                                            <?php if($type == $eqrow['type']){echo '<span class="buttontext">' . $option . '</span>';}?>
                                                        <?php endforeach;?>    
                                                    <?php endforeach;?>
                                                    &nbsp;&nbsp;&nbsp;<span class="caret"></span>
                                                </button>
                                                
                                                <ul class="dropdown-menu" style="z-index: 1000">
                                                    
                                                    <?php foreach($groups as $name => $group):?>
                                                        <small><?php echo $name;?></small>
                                                       
                                                       <?php foreach($group as $type => $option):?>
                                                            <li>
                                                                <a href="#" class="questionType" aria-data-value="<?php echo $type; ?>" <?php if($type == $eqrow['type']){echo 'active';}?>><?php echo $option;?></a>
                                                            </li>
                                                        <?php endforeach;?>    
                                                        
                                                        <li role="separator" class="divider"></li>
                                                    <?php endforeach;?>
                                                    
                                                </ul>
                                            </div>
                                        <?php else: ?>
                                            <?php 
                                                $aQtypeData=array();
                                                foreach (getQuestionTypeList($eqrow['type'], 'array') as $key=> $questionType)
                                                {
                                                    $aQtypeData[]=array('code'=>$key,'description'=>$questionType['description'],'group'=>$questionType['group']);
                                                }
                                                echo CHtml::dropDownList(
                                                                            'type',
                                                                            'category',
                                                                            CHtml::listData($aQtypeData,'code','description','group'),
                                                                            array(
                                                                                    'class' => 'none',
                                                                                    'id'=>'question_type',
                                                                                    'options' => array($eqrow['type']=>array('selected'=>true))
                                                                                 )
                                                                        );
                                            ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div  class="form-group">
                                        <label class="col-sm-4 control-label" for='gid'><?php eT("Question group:"); ?></label>
										<div class="col-sm-8">
											<select name='gid' id='gid' class="form-control">
												<?php echo getGroupList3($eqrow['gid'],$surveyid); ?>
											</select>
										</div>
                                    </div>

                                    <div  class="form-group" id="OtherSelection">
                                        <label class="col-sm-4 control-label"><?php eT("Option 'Other':"); ?></label>
                                        <?php if ($activated != "Y"): ?>
                                            <label for='OY'><?php eT("Yes"); ?></label><input id='OY' type='radio' class='radiobtn' name='other' value='Y'
                                                <?php if ($eqrow['other'] == "Y") { ?>
                                                    checked
                                                    <?php } ?>
                                                />&nbsp;&nbsp;
                                            <label for='ON'><?php eT("No"); ?></label><input id='ON' type='radio' class='radiobtn' name='other' value='N'
                                                <?php if ($eqrow['other'] == "N" || $eqrow['other'] == "" ) { ?>
                                                    checked='checked'
                                                    <?php } ?>
                                                />                                                
                                        <?php else:?>
                                            <?php eT("Cannot be changed (survey is active)");?>    
                                            <input type='hidden' name='other' value="<?php echo $eqrow['other']; ?>" />                                     
                                        <?php endif;?>
                                    </div>    

                                    <div id='MandatorySelection' class="form-group">
                                        <label class="col-sm-4 control-label"><?php eT("Mandatory:"); ?></label>
										<div class="col-sm-8">
											<?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'mandatory', 'value'=> $eqrow['mandatory'] === "Y"));?>
										</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for='relevance'><?php eT("Relevance equation:"); ?></label>
										<div class="col-sm-8">
											<textarea class="form-control" rows='1' id='relevance' name='relevance' ></textarea>
										</div>
                                    </div>

                                    <div id='Validation'  class="form-group">
                                        <label class="col-sm-4 control-label" for='preg'><?php eT("Validation:"); ?></label>
										<div class="col-sm-8">
											<input type='text' id='preg' name='preg' size='50' value="<?php echo $eqrow['preg']; ?>" />
										</div>
                                    </div>
                                </div>     
                            </div>
                        </div>
                    </div>
                
                
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="headingTwo">
                            <h4 class="panel-title">
                                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <?php eT("Advanced settings"); ?>
                                </a>
                            </h4>
                        </div>
                        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                            <div class="panel-body">
            
                                
                                <div id="advancedquestionsettingswrapper" >
                                    <div class="loader">
                                        <?php eT("Loading..."); ?>
                                    </div>
                                
                                    <div id="advancedquestionsettings"> 
                                        <!-- Content append via ajax -->
                                    </div>
                                </div>
                                
                                <br />
                            <br/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
                
                
                
                
                
                
            </div>			        
			        
			        	

                <?php if ($adding)
                    { ?>
                    <input type='hidden' name='action' value='insertquestion' />
                    <input type='hidden' name='gid' value='<?php echo $eqrow['gid']; ?>' />
                    <p><input type='submit' value='<?php eT("Add question"); ?>' />
                    <?php }
                    elseif ($copying)
                    { ?>
                    <input type='hidden' name='action' value='copyquestion' />
                    <input type='hidden' id='oldqid' name='oldqid' value='<?php echo $qid; ?>' />
                    <p><input type='submit' value='<?php eT("Copy question"); ?>' />
                    <?php }
                    else
                    { ?>
                    <input type='hidden' name='action' value='updatequestion' />
                    <input type='hidden' id='qid' name='qid' value='<?php echo $qid; ?>' />
                    <p><button type='submit' class="saveandreturn hidden" name="redirection" value="edit"><?php eT("Save") ?> </button>
                    <input type='submit'  class="hidden" value='<?php eT("Save and close"); ?>' />
                    <?php } ?>			        	
			        
			         <input type='hidden' id='sid' name='sid' value='<?php echo $surveyid; ?>' />	
			   </form>
			
			
			
			
			
			
		</div>
		<?php endif;?>
	</div>
</div>







