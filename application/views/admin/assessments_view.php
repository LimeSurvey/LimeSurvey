<?php
/**
 * Assesments view
 * HTML start in controller
 */
?>
<?php echo PrepareEditorScript(false, $this); ?>
<script type="text/javascript">
    <!--
        var strnogroup='<?php eT("There are no groups available.", "js");?>';
    -->
</script>

<h4><?php eT("Assessment rules");?></h4>

<!-- List assesments -->
<table class='table'>

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
               <th><?php echo $head; ?></th>
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
                        <?php
                            echo CHtml::link(
                                '<span data-toggle="tooltip" data-placement="bottom" title="" class="ui-pg-button icon-edit" onclick="" data-original-title="' . gT('Edit assessment') . '"></span>',
                                array("admin/assessments","sa"=>"index","surveyid"=>$surveyid,"action"=>'assessmentedit','id'=>$assess['id'])
                            );
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (Permission::model()->hasSurveyPermission($surveyid, 'assessments','delete')):  ?>
                        <div class='pull-left'>
                        <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post');?>
                            <span class="ui-pg-button glyphicon text-danger glyphicon-trash" data-toggle="tooltip" data-placement="bottom" title="" onclick='if (confirm("<?php eT("Are you sure you want to delete this entry?","js");?>")) { $(this).parent().submit(); }' data-original-title="<?php echo eT("Delete assessment"); ?>">
                            </span>
                             <input type='hidden' name='action' value='assessmentdelete' />
                             <input type='hidden' name='id' value='<?php echo $assess['id'];?>' />
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
                    <td><?php eT("Total");?></td>
                    <td>-</td>
                <?php } else { ?>
                    <td><?php eT("Question group");?></td>
                    <td><?php echo $groups[$assess['gid']]." (".$assess['gid'].")";?></td>
                <?php } ?>

                <!-- minimum -->
                <td><?php echo $assess['minimum'];?></td>

                <!-- maximum -->
                <td><?php echo $assess['maximum'];?></td>

                <!-- Score of the current group -->
                <td>
                    <?php
                    $aReplacement=array('PERC'=>gt('Score of the current group'),'TOTAL'=>gt('Total score'));
                    templatereplace($assess['name'],$aReplacement);
                    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
                    ?>
                </td>

                <!-- message -->
                <td>
                    <?php
                    templatereplace($assess['message'],$aReplacement);
                    echo FlattenText(LimeExpressionManager::GetLastPrettyPrintExpression(), true);
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Edition -->
<?php if ((Permission::model()->hasSurveyPermission($surveyid, 'assessments','update') && $actionvalue=="assessmentupdate") || (Permission::model()->hasSurveyPermission($surveyid, 'assessments','create')&& $actionvalue=="assessmentadd")): ?>
    <br />
    <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post', array('class'=>'form-horizontal','id'=>'assessmentsform','name'=>'assessmentsform', 'role' => 'form'));?>
        <h4><?php echo $actiontitle;?></h4>

            <!-- Scope, Total, Group -->
            <div class='form-group'>
                <label class='control-label col-sm-2'><?php eT("Scope:");?></label>
                <div class='col-sm-10'>
                    <div class='radio'>
                        <label class='radio-inline'><input class='' type='radio' id='radiototal' name='scope' value='T' <?php if (!isset($editdata) || $editdata['scope'] == "T") {echo "checked='checked' ";} ?>/><?php eT("Total"); ?></label>
                    </div>

                    <div class='radio'>
                        <label class='radio-inline'><input class='' type='radio' id='radiogroup' name='scope' value='G' <?php if (isset($editdata) && $editdata['scope'] == "G") {echo " checked='checked' ";} ?>/><?php eT("Group"); ?></label>
                    </div>
                </div>
            </div>

            <!-- Question group -->
            <div class='form-group'>
                <label class='control-label col-sm-2' for='gid'><?php eT("Question group:");?></label>
                <div class='col-sm-4'>
                    <?php
                    if (isset($groups))
                    { ?>
                        <select name='gid' id='gid' class="form-control">
                            <?php
                            foreach ($groups as $groupId => $groupName) {
                                echo '<option value="' . $groupId . '"'.(isset($editdata['gid']) && $editdata['gid']== $groupId ? ' selected' : '').'>' . flattenText($groupName) . '</option>';
                            }
                            ?>
                        </select>
                    <?php
                    }
                    else
                        echo eT("No question group found.");
                    ?>
                </div>
                <div class='col-sm-6'></div>
            </div>

            <!-- Minimum -->
            <div class='form-group'>
                <label class='control-label col-sm-2' for='minimum'><?php eT("Minimum:");?></label>
                <div class='col-sm-4'>
                    <input class='form-control' type='text' id='minimum' name='minimum' class='numbersonly'<?php if (isset($editdata)) {echo " value='{$editdata['minimum']}' ";} ?>/>
                </div>
                <div class='col-sm-6'></div>
            </div>

            <!-- Maximum -->
            <div class='form-group'>
                <label class='control-label col-sm-2' for='maximum'><?php eT("Maximum:");?></label>
                <div class='col-sm-4'>
                    <input class='form-control' type='text' id='maximum' name='maximum' class='numbersonly'<?php if (isset($editdata)) {echo " value='{$editdata['maximum']}' ";} ?>/>
                </div>
                <div class='col-sm-6'></div>
            </div>

        <!-- Languages tabs -->
        <div id="languagetabs">
            <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                <?php foreach ($assessmentlangs as $assessmentlang)
                {
                    $position=0;
                    echo '<li role="presentation" class="';
                    if ($assessmentlang==$baselang)
                    {
                        echo 'active';
                    }
                    echo '">
                            <a data-toggle="tab"  href="#tablang'.$assessmentlang.'">'.getLanguageNameFromCode($assessmentlang, false);
                    if ($assessmentlang==$baselang)
                    {
                        echo ' ('.gT("Base language").')';
                    }
                    echo '</a></li>';
                } ?>
            </ul>

            <div class="tab-content">

                <?php
                $count = 0;
                foreach ($assessmentlangs as $assessmentlang)
                {
                    $heading=''; $message='';
                    if ($action == "assessmentedit")
                    {
                        $results = Assessment::model()->findAllByAttributes(array('id' => $editId, 'language' => $assessmentlang));
                        foreach ($results as $row)
                        {
                            $editdata=$row->attributes;
                        }
                        $heading=htmlspecialchars($editdata['name'],ENT_QUOTES);
                        $message=htmlspecialchars($editdata['message']);
                    } ?>
                    <div id="tablang<?php echo $assessmentlang;?>" class="tab-pane fade in <?php if($count==0){ echo " active "; $count++;}?>">
                        <div class='col-sm-12'></div>
                        <div class='form-group'>
                            <label class='control-label col-sm-2' for='name_<?php echo $assessmentlang;?>'><?php eT("Heading");?>:</label>
                            <div class='col-sm-4'>
                                <input class='form-control' type='text' name='name_<?php echo $assessmentlang;?>' id='name_<?php echo $assessmentlang;?>' size='80' value='<?php echo $heading;?>'/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='control-label col-sm-2' for='assessmentmessage_<?php echo $assessmentlang;?>'><?php eT("Message");?>:</label>
                            <div class='col-sm-4'>
                                <textarea name='assessmentmessage_<?php echo $assessmentlang;?>' id='assessmentmessage_<?php echo $assessmentlang;?>' rows='10' cols='80'><?php echo $message;?></textarea>
                            </div>
                        </div>
                        <?php echo getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".gT("Message:", "js")."]",$surveyid,$gid,null,$action); ?>
                        <div class='form-group'>
                            <div class='col-sm-2'></div>
                            <div class='col-sm-4'>
                                <input type='submit' class="btn btn-default hidden" value='<?php eT("Save");?>'/>
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
    </form>
<?php endif; ?>
</div></div></div><!-- opened in controller -->
