<div class="side-body" id="edit-question-body">
    <h3>
        <?php echo $pageTitle; ?>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Result of modal actions (like replace labelset) -->
            <div id="dialog-result" title="Query Result" style='display:none;' class="alert alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                <span id="dialog-result-content">
                </span>
            </div>

            <div id="dialog-duplicate" title="<?php eT('Duplicate label set name'); ?>" style='display:none;' class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                <p>
                    <?php eT('Sorry, the name you entered for the label set is already in the database. Please select a different name.'); ?>
                </p>
            </div>

            <?php echo CHtml::form(array("admin/database"), 'post', array('id'=>$formId, 'name'=>$formName)); ?>

                <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
                <input type='hidden' name='qid' value='<?php echo $qid; ?>' />

                <?php if($viewType=='subQuestions'): ?>
                    <input type='hidden' id='action' name='action' value='updatesubquestions' />
                <?php elseif($viewType=='answerOptions'): ?>
                    <input type='hidden' name='action' value='updateansweroptions' />
                <?php endif; ?>

                <input type='hidden' name='sortorder' value='' />
                <input type='hidden' id='deletedqids' name='deletedqids' value='' />

                <?php $first=true; ?>

                <!-- Tabs -->
                <ul class="nav nav-tabs">
                    <?php foreach ($anslangs as $i => $anslang):?>
                        <li role="presentation" <?php if($i==0){echo 'class="active"';}?>>
                            <a data-toggle="tab" href='#tabpage_<?php echo $anslang; ?>'><?php echo getLanguageNameFromCode($anslang, false); ?>
                                <?php if ($anslang==Survey::model()->findByPk($surveyid)->language):?>
                                    (<?php echo gT("Base language"); ?>)
                                <?php endif;?>
                            </a>
                        </li>
                    <?php endforeach;?>
                </ul>
                <?php
                    $sortorderids='';
                    $codeids='';
                ?>

                <!-- Tab content -->
                <div class="tab-content">
                    <?php foreach ($anslangs as $i => $anslang):?>
                        <div id='tabpage_<?php echo $anslang; ?>' class='tab-page tab-pane fade in <?php if($i==0){echo 'active';}?>'>
                            <?php for ($scale_id = 0; $scale_id < $scalecount; $scale_id++): ?>
                                <?php
                                    $result = $results[$anslang][$scale_id];
                                    $anscount = count($result);
                                ?>

                                <?php // TODO : check the rendering of XSCALES / Y SCALES ?>

                                <?php // For subQuestions ?>
                                <?php if($viewType=='subQuestions'): ?>
                                    <?php $position=0; ?>
                                    <?php if ($scalecount>1): ?>
                                        <?php if ($scale_id==0): ?>
                                            <div class='header ui-widget-header'>
                                                <?php eT("Y-Scale"); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class='header ui-widget-header'>
                                                <?php eT("X-Scale"); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                <?php // For answers ?>
                                <?php elseif($viewType=='answerOptions'): ?>
                                    <?php $position=1; ?>
                                    <?php  if ($scalecount>1): ?>
                                        <div class='header ui-widget-header' style='margin-top:5px;'>
                                            <?php echo sprintf(gT("Answer scale %s"),$scale_id+1); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Answers/Subquestions Table -->
                                <table class='answertable table table-responsive' id='<?php echo $tableId[$anslang][$scale_id]; ?>' data-scaleid='<?php echo $scale_id; ?>'>

                                    <!-- Headers -->
                                    <thead>
                                        <tr>
                                            <th class="col-md-1">
                                                <?php if( $first && $activated != 'Y'): ?>
                                                    <?php eT("Position");?>
                                                <?php else: ?>
                                                    &nbsp;
                                                <?php endif; ?>
                                            </th>
                                            <th class='col-md-1'><?php eT("Code"); ?></th>

                                            <!-- subQuestions headers -->
                                            <?php if($viewType=='subQuestions'): ?>
                                                <th>
                                                    <?php eT("Subquestion"); ?>
                                                </th>
                                                <?php if ($first): ?>
                                                    <th id='rel-eq-th' class='col-md-1'>
                                                        <?php eT("Relevance equation"); ?>
                                                    </th>
                                                    <th class="col-md-1">
                                                        <?php eT("Action"); ?>
                                                    </th>
                                                <?php endif; ?>

                                            <!-- answer Options header-->
                                            <?php elseif($viewType=='answerOptions'): ?>
                                                <?php if ($assessmentvisible): ?>
                                                    <th class='col-md-1'>
                                                        <?php eT("Assessment value"); ?>
                                                    </th>
                                                <?php else: ?>
                                                    <th style='display:none;'>
                                                        &nbsp;
                                                    </th>
                                                <?php endif; ?>

                                                <th class='col-md-8'>
                                                    <?php eT("Answer option"); ?>
                                                </th>

                                                <th class='col-md-1'>
                                                    <?php if( $first): ?>
                                                        <?php eT("Actions"); ?>
                                                    <?php endif;?>
                                                </th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>

                                    <!-- Body -->
                                    <tbody id="rowcontainer">
                                        <?php foreach ($result as $row): ?>

                                            <!-- Line tag -->
                                            <?php if($viewType=='subQuestions'): ?>
                                                <tr id='row_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'>
                                                <?php $title = $row->title;?>
                                            <?php elseif($viewType=='answerOptions'):?>
                                                <tr class='row_<?php echo $position; ?>'>
                                                <?php $title = $row->code;?>
                                            <?php endif; ?>

                                            <!-- Move icon -->
                                            <?php if ($activated == 'Y' && $viewType=='subQuestions' ): ?>
                                                <td>
                                                    &nbsp;
                                                </td>
                                                <td  style="vertical-align: middle;">
                                                    <input
                                                        type='hidden'

                                                        name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                        value="<?php echo $title; ?>"
                                                        maxlength='20'
                                                        size='5'
                                                    />
                                                    <?php echo $title; ?>
                                                </td>
                                            <?php elseif (($activated != 'Y' && $first) || ($viewType=='answerOptions' && $first) ): // If survey is not activated and first language ?>
                                                <?php if($title) {$sPattern="^([a-zA-Z0-9]*|{$title})$";}else{$sPattern="^[a-zA-Z0-9]*$";} ?>
                                                <td>
                                                    <span class="glyphicon glyphicon-move"></span>
                                                </td>

                                                <td  style="vertical-align: middle;">
                                                    <?php
                                                        // TODO : check if possible to remove the viewType condition here
                                                        // implies : check if $row->qid == $position  && if onkeypress can be applied to subQuestions
                                                    ?>
                                                    <?php if($viewType=='subQuestions'): ?>
                                                        <input
                                                            type='hidden'
                                                            class='oldcode'
                                                            id='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                            name='oldcode_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                            value="<?php echo $title; ?>"
                                                        />
                                                        <input
                                                            type='text'
                                                            class="code form-control input-lg"
                                                            id='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                            class='code'
                                                            name='code_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                            value="<?php echo $title; ?>"
                                                            maxlength='20' size='20'
                                                            pattern='<?php echo $sPattern; ?>'
                                                            required='required'
                                                        />
                                                        <?php elseif($viewType=='answerOptions'):?>
                                                        <input
                                                            type='hidden'
                                                            class='oldcode'
                                                            id='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            name='oldcode_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            value="<?php echo $title; ?>"
                                                        /><input
                                                            type='text'
                                                            class='code form-control input-lg'
                                                            id='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            name='code_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            value="<?php echo $title; ?>"
                                                            maxlength='5' size='20' required
                                                            onkeypress="return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')"
                                                        />

                                                    <?php endif; ?>
                                                </td>
                                            <?php else:?>
                                                <td>&nbsp</td>
                                                <td  style="vertical-align: middle;">
                                                    <?php echo $title; ?>
                                                </td>
                                            <?php endif; ?>


                                            <!-- Assessment Value -->
                                            <?php if($viewType=='subQuestions'): ?>
                                                <!-- No assessment values for subQuestions -->
                                            <?php elseif($viewType=='answerOptions'):?>
                                                <?php if ($assessmentvisible && $first): ?>
                                                    <td>
                                                        <input
                                                            type='text'
                                                            class='assessment form-control input-lg'
                                                            id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            value="<?php echo $row->assessment_value; ?>"
                                                            maxlength='5'
                                                            size='5'
                                                            onkeypress="return goodchars(event,'-1234567890')"
                                                        />
                                                    </td>
                                                <?php elseif ( $first): ?>
                                                    <td style='display:none;'>
                                                        <input
                                                            type='text'
                                                            class='assessment'
                                                            id='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            name='assessment_<?php echo $position; ?>_<?php echo $scale_id; ?>'
                                                            value="<?php echo $row->assessment_value; ?>" maxlength='5' size='5'
                                                            onkeypress="return goodchars(event,'-1234567890')"
                                                        />
                                                    </td>
                                                <?php elseif ($assessmentvisible): ?>
                                                    <td>
                                                        <?php echo $row['assessment_value']; ?>
                                                    </td>
                                                <?php else: ?>
                                                    <td style='display:none;'>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endif;?>

                                            <!-- Question / Answer -->
                                            <?php
                                                // TODO : remove this if statement, and merge the two td
                                                // implies : define in controller $answer_id for each row (answer_<?php echo $row->language; ? >_<?php echo $row->qid; ? >_<?php echo $row->scale_id; ? >)
                                                // and : define getEditor paramaters in controller for each row
                                                // and : check if the onkeypress event makes sense for answer options
                                            ?>
                                            <?php if($viewType=='subQuestions'): ?>
                                                <td style="vertical-align: middle;">
                                                    <input
                                                        type='text'
                                                        size='20'
                                                        class='answer form-control input-lg'
                                                        id='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                        name='answer_<?php echo $row->language; ?>_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>'
                                                        placeholder='<?php eT("Some example subquestion","js") ?>'
                                                        value="<?php echo $row->question; ?>"
                                                        onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}"
                                                        />
                                                </td>
                                            <?php elseif($viewType=='answerOptions'): ?>
                                                <td style="vertical-align: middle;">
                                                    <input
                                                        type='text'
                                                        size='20'
                                                        class='answer form-control input-lg'
                                                        id='answer_<?php echo $row->language; ?>_<?php echo $row->sortorder; ?>_<?php echo $scale_id; ?>'
                                                        name='answer_<?php echo $row->language; ?>_<?php echo $row->sortorder; ?>_<?php echo $scale_id; ?>'
                                                        placeholder='<?php eT("Some example answer option","js") ?>'
                                                        value="<?php echo $row->answer; ?>"
                                                    />
                                                </td>
                                            <?php endif;?>

                                            <!-- Relevance equation -->
                                            <?php if($viewType == 'subQuestions'): ?>
                                                <?php if ($first):  /* default lang - input field */?>
                                                    <td>
                                                        <input data-toggle="tooltip" data-title="<?php eT("Click to expand"); ?>" type='text' class='relevance form-control input-lg' id='relevance_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' name='relevance_<?php echo $row->qid; ?>_<?php echo $row->scale_id; ?>' value="<?php echo $row->relevance; ?>" onkeypress=" if(event.keyCode==13) { if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_<?php echo $anslang; ?>').click(); return false;}" />
                                                    </td>
                                                <?php else:       /* additional language: just print rel. equation */  ?>
                                                    <span style="display: none" class="relevance"> <?php echo $row->relevance; ?> </span>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <!-- Icons edit/delete -->
                                            <td style="vertical-align: middle;" class="subquestion-actions">

                                                <?php echo  getEditor("editanswer","answer_".$row->language."_".$row->qid."_{$row->scale_id}", "[".gT("Subquestion:", "js")."](".$row->language.")",$surveyid,$gid,$qid,'editanswer'); ?>

                                                <?php if ( ($activated != 'Y' && $first) ||  ($viewType=='answerOptions' && $first)  ):?>
                                                    <?php
                                                        // TODO : remove this if statement, and merge the two td
                                                        // implies : define in controller titles
                                                    ?>
                                                    <?php if($viewType=='subQuestions'): ?>
                                                        <span class="icon-add text-success btnaddanswer" data-code="<?php echo $title; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Insert a new subquestion after this one") ?>"></span>
                                                        <span class="glyphicon glyphicon-trash text-danger btndelanswer"  data-toggle="tooltip" data-placement="bottom" title="<?php eT("Delete this subquestion") ?>"></span>
                                                    <?php elseif($viewType=='answerOptions'): ?>
                                                        <span class="icon-add text-success btnaddanswer"  data-code="<?php echo $title; ?>" data-toggle="tooltip" data-placement="bottom" title="<?php eT("Insert a new answer option after this one") ?>"></span>
                                                        <span class="glyphicon glyphicon-trash text-danger btndelanswer" data-toggle="tooltip" data-placement="bottom"  title="<?php eT("Delete this answer option") ?>"></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- Relevance : only for subQuestion. -->
                                                <?php if($viewType=='subQuestions'): ?>
                                                    <?php if ($scale_id==0):   /* relevance column */ ?>
                                                        <?php if($first): ?>
                                                            <!-- Don't need toggle icon -->
                                                        <?php endif;?>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <?php $position++; ?>

                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <div class="action-buttons">
                                    <?php if($viewType=='subQuestions'): ?>
                                        <?php
                                            $disabled = ($activated == 'Y')?$disabled="disabled='disabled'":'';
                                        ?>
                                    <?php elseif($viewType=='answerOptions'): ?>
                                        <?php if ($first): ?>
                                            <input type='hidden' id='answercount_<?php echo $scale_id; ?>' name='answercount_<?php echo $scale_id; ?>' value='<?php echo $anscount; ?>' />
                                            <?php $disabled=""; ?>
                                        <?php endif; ?>
                                        <br/>
                                    <?php endif;?>

                                    <button <?php echo $disabled; ?>  id='btnlsbrowser_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btnlsbrowser btn btn-default' type='button'    data-toggle="modal" data-target="#labelsetbrowserModal">
                                        <?php eT('Predefined label sets...'); ?>
                                    </button>

                                    <button <?php echo $disabled; ?>  id='btnquickadd_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' class='btn btn-default' type='button'  data-toggle="modal" data-target="#quickaddModal">
                                        <?php eT('Quick add...'); ?>
                                    </button>


                                    <?php if(Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('labelsets','create')): ?>
                                        <button class='bthsaveaslabel btn btn-default' id='bthsaveaslabel_<?php echo $scale_id; ?>' type='button' data-toggle="modal" data-target="#saveaslabelModal">
                                            <?php eT('Save as label set'); ?>
                                        </button>
                                    <?php endif; ?>

                                </div>
                                <?php $position=sprintf("%05d", $position); ?>
                            <?php endfor;?>
                        </div>
                        <?php $first=false; ?>
                    <?php endforeach; ?>


                    <!-- Modals -->
                    <?php $this->renderPartial("./survey/Question/question_subviews/_modals", array()); ?>

                    <p>
                        <input type='submit' class="hidden" id='saveallbtn_<?php echo $anslang; ?>' name='method' value='<?php eT("Save changes"); ?>' />
                    </p>

                </div>
                <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />

            </form>
        </div>
    </div>
</div>
