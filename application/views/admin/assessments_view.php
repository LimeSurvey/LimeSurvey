<?php
/**
* Assesments view
* HTML start in controller
*/
?>
  <div class="side-body <?=getSideBodyClass(false)?>">
    <?=viewHelper::getViewTestTag('surveyAssessments');?>
      <h3 class="page-title"><?=gT("Assessments")?></h3>
  <?php echo PrepareEditorScript(false, $this); ?>
    <script type="text/javascript">
      <!--
      var strnogroup = '<?php eT("There are no groups available.", "js");?>';
      -->
    </script>
    <div class="container-fluid">
    <?php if($asessementNotActivated):?>
        <div class="row text-center">
          <div class="jumbotron message-box <?php echo isset($asessementNotActivated['class']) ? $asessementNotActivated['class'] : " "; ?>">
            <h2><?php echo $asessementNotActivated['title'];?></h2>
            <?php echo $asessementNotActivated['message'];?>
          </div>
        </div>
    </form>
<?php else:?>
    <h4><?php eT("Assessment rules");?></h4>
        <div class="row">
            <div class="col-sm-12">
            <?php $this->widget('bootstrap.widgets.TbGridView', array(
					'dataProvider' => $model->search(),
					// Number of row per page selection
					'id' => 'assessements-grid',
					'columns' => $model->getColumns(),
					'filter' => $model,
					'emptyText'=>gT('No customizable entries found.'),
					'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
						CHtml::dropDownList(
							'pageSizeAsessements',
							$pageSizeAsessements,
							Yii::app()->params['pageSizeOptions'],
							array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
						)
					),
					'rowHtmlOptionsExpression' => '["data-assessement-id" => $data->id]',
					'htmlOptions' => array('class'=> 'table-responsive'),
					'itemsCssClass' => 'table table-responsive table-striped',
					'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view'),
					'ajaxType' => 'POST',
                    'ajaxUpdate' => 'assessements-grid',
                    'template'  => "{items}\n<div id='tokenListPager'><div class=\"col-sm-4\" id=\"massive-action-container\"></div><div class=\"col-sm-4 pager-container ls-ba \">{pager}</div><div class=\"col-sm-4 summary-container\">{summary}</div></div>",
    				'afterAjaxUpdate'=>'bindAction',
				));
            ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <button class="btn btn-success" id="selector__assessement-add-new"><?=eT("Add new assessment rule")?></button>
            </div>
        </div>
<?php /*
                <!-- List assesments -->
                <table class='table table-striped' id="selector__assessement-table">
                    <!-- header -->
                    <thead>
                        <tr>
                            <th>
                                <?php eT("ID");?>
                            </th>
                            <th>
                                <?php eT("Actions");?>
                            </th>
                            <th>
                                <?php eT("SID");?>
                            </th>

                            <?php foreach ($headings as $head):?>
                                <th>
                                    <?php echo $head; ?>
                                </th>
                            <?php endforeach; ?>

                            <th>
                                <?php eT("Title");?>
                            </th>

                            <th>
                                <?php eT("Message");?>
                            </th>
                        </tr>
                    </thead>

                    <!-- body -->
                    <tbody>
                    <?php foreach($assessments as $assess): ?>
                        <tr>
                            <!-- ID -->
                            <td>
                                <?php echo $assess['id'];?>
                            </td>

                            <!-- Actions -->
                            <td>
                                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'assessments','update')): ?>
                                    <div class='pull-left'>
                                    <a 
                                        href="<?=Yii::app()->createUrl("admin/assessments",["sa"=>"index","surveyid"=>$surveyid, "action"=>'assessmentedit','id'=>$assess['id']])?>" 
                                        class="btn btn-default btn-xs"
                                        data-toggle="tooltip" 
                                        data-placement="bottom"  
                                        title="<?=gT('Edit assessment')?>">
                                        <i class="icon-edit"></i>
                                    </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (Permission::model()->hasSurveyPermission($surveyid, 'assessments','delete')):  ?>
                                    <div class='pull-left'>
                                    <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post');?>
                                        <button class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo eT("Delete assessment "); ?>" data-placement="bottom" onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?","js");?>")) { $(this).parent().submit(); }'>
                                        <i class="fa text-danger fa-trash"></i>
                                        <input type='hidden' name='action' value='assessmentdelete' />
                                            <input type='hidden' name='id' value='<?php echo $assess['id'];?>' />
                                        </button>
                                    </form>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- SID -->
                            <td>
                                <?php echo $assess['sid'];?>
                            </td>

                            <!-- Total || Question group -->
                            <?php if ($assess['scope'] == "T") { ?>
                                <td>
                                    <?php eT("Total");?>
                                </td>
                                <td>-</td>
                            <?php } else { ?>
                                <td>
                                    <?php eT("Question group");?>
                                </td>
                                <td>
                                    <?php echo $groups[$assess['gid']]." (".$assess['gid'].")";?>
                                </td>
                            <?php } ?>

                            <!-- minimum -->
                            <td>
                                <?php echo $assess['minimum'];?>
                            </td>

                            <!-- maximum -->
                            <td>
                                <?php echo $assess['maximum'];?>
                            </td>

                            <!-- Score of the current group -->
                            <td>
                                <?php $aReplacement=array('PERC'=>gt('Score of the current group'),'TOTAL'=>gt('Total score'));
                                    templatereplace($assess['name'],$aReplacement);
                                    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
                                ?>
                            </td>

                            <!-- message -->
                            <td>
                                <?php templatereplace($assess['message'],$aReplacement);
                                    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
*/ ?>
                <!-- Edition - Modal -->
                <?php if ((Permission::model()->hasSurveyPermission($surveyid, 'assessments','update') && $actionvalue=="assessmentupdate") || (Permission::model()->hasSurveyPermission($surveyid, 'assessments','create')&& $actionvalue=="assessmentadd")): ?>
                <div id="assesements-edit-add" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post', array('class'=>'form','id'=>'assessmentsform','name'=>'assessmentsform', 'role' => 'form'));?>
                                <div class="modal-header"> 
                                    <h4 class="modal-title"> 
                                        <?php echo $actiontitle;?> 
                                    </h4> 
                                </div>
                                <div class="modal-body">
                                    <!-- Scope, Total, Group -->
                                    <div class='row'>
                                        <div class='form-group col-md-12'>
                                            <label class='control-label col-sm-2'>
                                                <?php eT("Scope:");?>
                                            </label>
                                            <div class='col-sm-10 ls-flex wrap'>
                                                <div class='col-md-4 col-sm-5 col-xs-6'>
                                                    <label class='radio-inline'>
                                                        <input class='' type='radio' id='radiototal' name='scope' value='T' <?php if (!isset($editdata) || $editdata['scope']=="T" ) {echo "checked='checked' ";} ?>/>
                                                        <?php eT("Total"); ?>
                                                    </label>
                                                </div>

                                                <div class='col-md-4 col-sm-5 col-xs-6'>
                                                    <label class='radio-inline'>
                                                        <input class='' type='radio' id='radiogroup' name='scope' value='G' <?php if (isset($editdata) && $editdata['scope']=="G" ) {echo " checked='checked' ";} ?>/>
                                                        <?php eT("Group"); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <!-- Question group -->
                                        <div class='form-group col-md-12'>
                                            <label class='control-label col-sm-2' for='gid'>
                                                <?php eT("Question group:");?>
                                            </label>
                                            <div class='col-sm-10'>
                                                <?php if (isset($groups)) { ?>
                                                    <select name='gid' id='gid' class="form-control">
                                                        <?php foreach ($groups as $groupId => $groupName) {
                                                            echo '<option value="' . $groupId . '"'.(isset($editdata['gid']) && $editdata['gid']== $groupId ? ' selected' : '').'>' . flattenText($groupName) . '</option>';
                                                        } ?>
                                                    </select>
                                                <?php } else {
                                                    echo eT("No question group found.");
                                                } ?>
                                            </div>
                                            <div class='col-sm-2 hide-xs'></div>
                                        </div>

                                    </div>
                                    <div class='row'>
                                        <!-- Minimum -->
                                        <div class='form-group col-md-12'>
                                            <label class='control-label col-sm-2' for='minimum'>
                                                <?php eT("Minimum:");?>
                                            </label>
                                            <div class='col-sm-10'>
                                                <input class='form-control' type='text' id='minimum' name='minimum' class='numbersonly' <?php if (isset($editdata)) {echo " value='{$editdata['minimum']}' ";} ?>/>
                                            </div>
                                            <div class='col-sm-2 hide-xs'></div>
                                        </div>

                                        <!-- Maximum -->
                                        <div class='form-group col-md-12'>
                                        <label class='control-label col-sm-2' for='maximum'>
                                            <?php eT("Maximum:");?>
                                        </label>
                                        <div class='col-sm-10'>
                                            <input class='form-control' type='text' id='maximum' name='maximum' class='numbersonly' <?php if (isset($editdata)) {echo " value='{$editdata['maximum']}' ";} ?>/>
                                        </div>
                                            <div class='col-sm-2 hide-xs'></div>
                                        </div>
                                    </div>

                                    <!-- Languages tabs -->
                                    <div id="languagetabs" class="row">
                                        <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                                            <?php foreach ($assessmentlangs as $assessmentlang) {
                                                $position=0;
                                                echo '<li role="presentation" class="'.($assessmentlang==$baselang ? 'active' : '').'">
                                                <a data-toggle="tab"  href="#tablang'.$assessmentlang.'">'.getLanguageNameFromCode($assessmentlang, false);
                                                if ($assessmentlang==$baselang) {
                                                    echo ' ('.gT("Base language").')';
                                                }
                                                echo '</a></li>';
                                            } ?>
                                        </ul>

                                        <div class="tab-content">
                                        <?php
                                            $count = 0;
                                            foreach ($assessmentlangs as $assessmentlang) {
                                                $heading=''; $message='';

                                                if ($action == "assessmentedit") {

                                                    $results = Assessment::model()->findAllByAttributes(array('id' => $editId, 'language' => $assessmentlang));
                                                    foreach ($results as $row) {
                                                        $editdata=$row->attributes;
                                                    }
                                                    $heading=htmlspecialchars($editdata['name'],ENT_QUOTES);
                                                    $message=htmlspecialchars($editdata['message']);
                                                } ?>

                                                <div id="tablang<?php echo $assessmentlang;?>" class="tab-pane fade in <?php if($count==0){ echo " active "; $count++;}?>">
                                                    <div class='col-sm-12'></div>
                                                    <div class='form-group col-sm-12'>
                                                        <label class='control-label col-sm-12' for='name_<?php echo $assessmentlang;?>'>
                                                        <?php eT("Heading");?>:</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='name_<?php echo $assessmentlang;?>' id='name_<?php echo $assessmentlang;?>' size='80' value='<?php echo $heading;?>' />
                                                        </div>
                                                    </div>
                                                    <div class='form-group col-sm-12'>
                                                        <label class='control-label col-sm-12' for='assessmentmessage_<?php echo $assessmentlang;?>'>
                                                        <?php eT("Message");?>:</label>
                                                        <div class='col-sm-12'>
                                                            <textarea name='assessmentmessage_<?php echo $assessmentlang;?>' id='assessmentmessage_<?php echo $assessmentlang;?>' rows='10' style="width:100%">
                                                                <?php echo $message;?>
                                                            </textarea>
                                                            <?php echo getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".gT("Message:", "js")."]",$surveyid,$gid,null,$action); ?>
                                                        </div>
                                                    </div>
                                                    <div class='form-group'>
                                                        <div class='col-sm-2'></div>
                                                        <div class='col-sm-4'>
                                                            <input type='submit' class="btn btn-default hidden" value='<?php eT("Save");?>' />
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php } ?>
                                        </div>
                                    </div>

                                    <!-- action buttons -->
                                    <div>
                                        <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
                                        <input type='hidden' name='action' value='<?php echo $actionvalue;?>' />
                                        <input type='hidden' name='id' value='<?php echo $editId;?>' />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-success" id="selector__assessements-save-modal" data-dismiss="modal">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- opened in controller -->
<?php endif;?>
</div>
