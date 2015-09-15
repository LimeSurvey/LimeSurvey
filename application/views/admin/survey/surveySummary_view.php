<div class="side-body">

    <h3><?php eT('Survey quick actions'); ?></h3>
        <div class="row welcome survey-action">
            <div class="col-lg-12 content-right">
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($activated == "Y"): ?>
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <strong><?php eT('Warning!');?></strong> <?php eT('While survey is activated, you can\'t add or remove group or question');?>
                            </div>                                
                        <?php elseif(!$groups_count > 0):?>
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <strong><?php eT('Warning!');?></strong> <?php eT('To add questions, first, you must add a question group.');?>
                            </div>
                                                        
                            <div class="alert alert-info alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;
                                <?php eT('If you want a single page survey, just add a single group, and switch on "Show questions group by group"');?>
                            </div>                                                                                                           
                        <?php endif;?>
                        
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <label for="groupbygroup"><?php eT('Show questions group by group :');?></label>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'groupbygroup'
                        ));?>
                        <br/><br/>
                    </div>
                </div>
                
                <div class="row">
                    <?php if ($activated == "Y"): ?>

                            <div class="col-lg-2">
                                <div class="panel panel-primary disabled" id="pannel-1">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><?php eT('Add group');?></h4>
                                </div>
                                <div class="panel-body">
                                    <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip">
                                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/328637/add.png" class="responsive"/>
                                    </a>
                                    <p><a href="#"><?php eT('Add new group to survey');?></a></p>
                                </div>          
                                </div>
                            </div>                        
                
                            <div class="col-lg-2" >
                                <div class="panel panel-primary disabled" id="pannel-2">
                                    <div class="panel-heading">
                                        <h4 class="panel-title  disabled"><?php eT('Add question');?></h4>
                                    </div>
                                    <div class="panel-body  ">
                                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip">
                                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/328637/add.png" class="responsive"/>
                                        </a>
                                        <p>
                                            <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is currently active."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                                                <?php eT("Add new question"); ?>
                                            </a>
                                        </p>
                                    </div>          
                                </div>
                            </div>            
                                  
                    <?php elseif(Permission::model()->hasSurveyPermission($surveyinfo['sid'],'surveycontent','create')): ?>

                        <div class="col-lg-2">
                            <div class="panel panel-primary panel-clickable" id="pannel-1" aria-data-url="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$surveyinfo['sid']); ?>">
                            <div class="panel-heading">
                                <h4 class="panel-title"><?php eT('Add group');?></h4>
                            </div>
                            <div class="panel-body">
                                <a  href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$surveyinfo['sid']); ?>" >
                                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/328637/add.png" class="responsive"/>
                                </a>
                                <p><a href="<?php echo $this->createUrl("admin/questiongroups/sa/add/surveyid/".$surveyinfo['sid']); ?>"><?php eT('Add new group to survey');?></a></p>
                            </div>          
                            </div>
                        </div>
                        
                        <?php if(!$groups_count > 0): ?>
                            
                            <div class="col-lg-2" >
                                <div class="panel panel-primary disabled" id="pannel-2">
                                    <div class="panel-heading">
                                        <h4 class="panel-title  disabled"><?php eT('Add question');?></h4>
                                    </div>
                                    <div class="panel-body  ">
                                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>">
                                            <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/328637/add.png" class="responsive"/>
                                        </a>
                                        <p>
                                            <a  href="#" data-toggle="tooltip" data-placement="bottom" title="<?php eT("You must first create a question group."); ?>" style="display: inline-block" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Survey cannot be activated. Either you have no permission or there are no questions.'); ?>" >
                                                <?php eT("Add new question"); ?>
                                            </a>
                                        </p>
                                    </div>          
                                </div>
                            </div>
                    
                                   
                        <?php else:?>
                            <div class="col-lg-2">
                                <div class="panel panel-primary panel-clickable" id="pannel-2" aria-data-url="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$surveyinfo['sid']); ?>">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><?php eT('Add question');?></h4>
                                </div>
                                <div class="panel-body">
                                    <a  href="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$surveyinfo['sid']); ?>" >
                                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/big/328637/add.png" class="responsive"/>
                                    </a>
                                    <p><a href="<?php echo $this->createUrl("admin/questions/sa/newquestion/surveyid/".$surveyinfo['sid']); ?>")"><?php eT("Add new question"); ?></a></p>
                                </div>          
                                </div>
                            </div>
                
                        <?php endif; ?>
                                
                    <?php endif; ?>
                </div>
        
        
                        
                        
            </div>
        </div>
     
	
	<h3><?php eT('Survey summary'); ?></h3>
		<div class="row">
			<div class="col-lg-12 content-right">
				<table class="items table" id='surveydetails'>
					<thead>
						<tr>
							<th><?php eT("Title");?></th>
							<th><?php echo flattenText($surveyinfo['surveyls_title'])." (".gT("ID")." ".$surveyinfo['sid'].")";?></th>
						</tr>
					</thead>
				    <tr>
				        <td>
				            <strong> <?php echo gT("Survey URL");?> :</strong>
				        </td>
				        <td>
				        </td>
				    </tr>
				    <tr>
				    	<td style="border-top: none; padding-left: 2em">
				    		<small><?php echo getLanguageNameFromCode($surveyinfo['language'],false); ?></small>
				    	</td>
				    	<td style="border-top: none;" >
					        <?php $tmp_url = $this->createAbsoluteUrl("survey/index",array("sid"=>$surveyinfo['sid'],"lang"=>$surveyinfo['language'])); ?>
					        <small><a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a></small>
				    	</td>
				    </tr>
				    
				        <?php
				        foreach ($aAdditionalLanguages as $langname)
				        {?>
				        <tr>
				            <td  style="border-top: none; padding-left: 2em">
				                <small><?php echo getLanguageNameFromCode($langname,false).":";?></small>
				            </td>
				            <td  style="border-top: none;" >
				            	<?php $tmp_url = $this->createAbsoluteUrl("/survey/index",array("sid"=>$surveyinfo['sid'],"lang"=>$langname)); ?>
				            	<small><a href='<?php echo $tmp_url?>' target='_blank'><?php echo $tmp_url; ?></a></small>
				            </td>
				        </tr>
				
				        <?php
				        } ?>
				    <tr>
				        <td   style="border-top: none; padding-left: 2em">
				            <small><?php eT("End URL");?>:</small>
				        </td>
				        <td style="border-top: none">
				            <small><?php echo $endurl;?></small>
				        </td>
				    </tr>						        
				    <tr>
				    	<td><strong><?php eT("Survey's texts");?> :</strong></td>
				    	<td></td>
				    </tr>
				    <tr>
				        <td style="border-top: none; padding-left: 2em">
				            <small><?php eT("Description:");?></small>
				        </td>
				        <td style="border-top: none;" >
				        	<small>
				            <?php
				                if (trim($surveyinfo['surveyls_description']) != '')
				                {
				                    templatereplace(flattenText($surveyinfo['surveyls_description']));
				                    echo LimeExpressionManager::GetLastPrettyPrintExpression();
				                }
				            ?>
				            </small>
				        </td>
				    </tr>
				    <tr>
				        <td style="border-top: none; padding-left: 2em">
				            <small><?php eT("Welcome:");?></small>
				        </td>
				        <td style="border-top: none;" >
				        	<small>
				            <?php
				                templatereplace(flattenText($surveyinfo['surveyls_welcometext']));
				                echo LimeExpressionManager::GetLastPrettyPrintExpression();
				            ?>
				            </small>
				        </td>
				    </tr>
				    <tr>
				        <td style="border-top: none; padding-left: 2em">
				            <small><?php eT("End message:");?></small>
				        </td>
				        <td style="border-top: none;" >
				        	<small>
				            <?php
				                templatereplace(flattenText($surveyinfo['surveyls_endtext']));
				                echo LimeExpressionManager::GetLastPrettyPrintExpression();
				            ?>
				            </small>
				        </td>
				    </tr>

					<tr>
						<td>
							<strong><?php eT('Languages');?>:</strong>
						</td>
						<td></td>
					</tr>
				    <tr>
				        <td style="border-top: none; padding-left: 2em">
				            <small><?php eT("Base language:");?></small>
				        </td>
				        <td style="border-top: none;" >
				            <small><?php echo $language;?></small>
				        </td>
				    </tr>
					<?php $count=0; ?>
				        <?php foreach ($aAdditionalLanguages as $langname): ?>
				        <tr>
							<?php if($count==0): ?>
							    <td style="border-top: none; padding-left: 2em">
							        <small><?php eT("Additional languages:");?>
							    </td>
							    <?php $count++;?>
							<?php else:?>
								<td style="border-top: none; padding-left: 2em"></td>									    
							<?php endif;?>
							
				            <td  style="border-top: none;">
				               <small> <?php echo getLanguageNameFromCode($langname,false);?></small>
				            </td>
				        </tr>
			        <?php endforeach;?>

				    
				    <tr>
				        <td>
				            <strong><?php eT("Administrator:");?></strong>
				        </td>
				        <td>
				            <?php echo flattenText("{$surveyinfo['admin']} ({$surveyinfo['adminemail']})");?>
				        </td>
				    </tr>
				    <?php if (trim($surveyinfo['faxto'])!='') { ?>
				        <tr>
				            <td>
				                <strong><?php eT("Fax to:");?></strong>
				            </td>
				            <td>
				                <?php echo flattenText($surveyinfo['faxto']);?>
				            </td>
				        </tr>
				    <?php } ?>
				    <tr>
				        <td>
				            <strong><?php eT("Start date/time:");?></strong>
				        </td>
				        <td>
				            <?php echo $startdate;?>
				        </td>
				    </tr>
				    <tr>
				        <td>
				            <strong><?php eT("Expiry date/time:");?></strong>
				        </td>
				        <td>
				            <?php echo $expdate;?>
				        </td>
				    </tr>
				    <tr>
				        <td>
				            <strong><?php eT("Template:");?></strong>
				        </td>
				        <td>
				            <?php echo $surveyinfo['template'];?>
				        </td>
				    </tr>

				    <tr>
				        <td>
				            <strong><?php eT("Number of questions/groups");?>:</strong>
				        </td>
				        <td>
				            <?php echo $sumcount3."/".$sumcount2;?>
				        </td>
				    </tr>
				    <tr>
				        <td>
				            <strong><?php eT("Survey currently active");?>:</strong>
				        </td>
				        <td>
				            <?php echo $activatedlang;?>
				        </td>
				    </tr>
				    <?php if($activated=="Y") { ?>
				    <tr>
				        <td>
				            <strong><?php eT("Survey table name");?>:</strong>
				        </td>
				        <td>
				            <?php echo $surveydb;?>
				        </td>
				    </tr>
				    <?php } ?>
				    <tr>
				        <td>
				            <strong><?php eT("Hints");?>:</strong>
				        </td>
				        <td>
				            <?php echo $warnings.$hints;?>
				        </td>
				    </tr>
				    <?php if ($tableusage != false){
				            if ($tableusage['dbtype']=='mysql' || $tableusage['dbtype']=='mysqli'){
				                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
				                $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2); ?>
				                <tr><td><strong><?php eT("Table column usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
				                <tr><td><strong><?php eT("Table size usage");?>: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $size_usage;?>'></div></td></tr>
				            <?php }
				            elseif (($arrCols['dbtype'] == 'mssql')||($arrCols['dbtype'] == 'postgre')||($arrCols['dbtype'] == 'dblib')){
				                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2); ?>
				                <tr><td><strong><?php eT("Table column usage");?>: </strong></td><td><strong><?php echo $column_usage;?>%</strong><div class='progressbar' style='width:20%; height:15px;' name='<?php echo $column_usage;?>'></div> </td></tr>
				            <?php }
				        } ?>
				</table>
			</div>
		</div>
</div>
