<?php
/*
* Assessments edit
*/
?>
  <div id="assesements-edit-add" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <?php echo CHtml::form(array("admin/assessments/sa/index/surveyid/{$surveyid}"), 'post', array('class'=>'form','id'=>'assessmentsform','name'=>'assessmentsform', 'role' => 'form'));?>
          <div class="modal-header">
            <h4 class="modal-title"> </h4>
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
                      <input class='' type='radio' id='radiototal' name='scope' value='T' checked='checked' />
                      <?php eT("Total"); ?>
                    </label>
                  </div>

                  <div class='col-md-4 col-sm-5 col-xs-6'>
                    <label class='radio-inline'>
                      <input class='' type='radio' id='radiogroup' name='scope' value='G' />
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
                        <?php foreach ($groups as $groupId => $groupName) { ?>
                        <option value="<?=$groupId?>" ><?=flattenText($groupName)?></option>
                        <?php } ?>
                    </select>
                    <?php } else { echo eT("No question group found."); } ?>
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
                  <input class='form-control numbersonly' type='text' id='minimum' name='minimum' />
                </div>
                <div class='col-sm-2 hide-xs'></div>
              </div>

              <!-- Maximum -->
              <div class='form-group col-md-12'>
                <label class='control-label col-sm-2' for='maximum'>
                  <?php eT("Maximum:");?>
                </label>
                <div class='col-sm-10'>
                  <input class='form-control numbersonly' type='text' id='maximum' name='maximum' />
                </div>
                <div class='col-sm-2 hide-xs'></div>
              </div>
            </div>

            <!-- Languages tabs -->
            <div id="languagetabs" class="row">
              <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                <?php foreach ($assessmentlangs as $assessmentlang) {
                $position=0;
                ?>
                <li role="presentation" class="<?=($assessmentlang==$baselang ? 'active' : '')?>">
                    <a data-toggle="tab"  href="#tablang<?=$assessmentlang?>">
                        <?php 
                        echo getLanguageNameFromCode($assessmentlang, false);
                            if ($assessmentlang==$baselang) {
                                echo ' ('.gT("Base language").')';
                            }
                        ?>
                    </a>
                </li>
                <?php } ?>
              </ul>

              <div class="tab-content">
                <?php
                    $count = 0;
                    foreach ($assessmentlangs as $assessmentlang) {
                        $heading=''; $message='';
                ?>
                  <div id="tablang<?=$assessmentlang;?>" class="tab-pane fade in <?php if($count==0){ echo " active "; $count++;}?>">
                    <div class='col-sm-12'></div>
                    <div class='form-group col-sm-12'>
                      <label class='control-label col-sm-12' for='name_<?=$assessmentlang?>'>
                        <?php eT("Heading");?>:</label>
                      <div class='col-sm-12'>
                        <input class='form-control' type='text' name='name_<?=$assessmentlang?>' id='name_<?=$assessmentlang?>>' size='80' value='<?=$heading?>' />
                      </div>
                    </div>
                    <div class='form-group col-sm-12'>
                      <label class='control-label col-sm-12' for='assessmentmessage_<?=$assessmentlang?>'>
                        <?php eT("Message");?>:</label>
                      <div class='col-sm-12'>
                        <div class="htmleditor input-group">
                          <textarea name='assessmentmessage_<?=$assessmentlang?>' class="form-control" id='assessmentmessage_<?=$assessmentlang?>' rows='10' ><?php echo $message;?></textarea>
                          <?php echo getEditor("assessment-text","assessmentmessage_".$assessmentlang, "[".gT("Message:", "js")."]",$surveyid,$gid,null,$action); ?>
                        </div>
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
              <input type='hidden' name='action' value='assessmentadd' />
              <input type='hidden' name='id' value='<?php echo $editId;?>' />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
            <button type="button" class="btn btn-success" id="selector__assessments-save-modal"><?php eT('Save'); ?></button>
          </div>
          </form>
      </div>
    </div>
  </div>
