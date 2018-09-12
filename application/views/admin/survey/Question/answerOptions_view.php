<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oQuestion'=>$oQuestion, 'active'=>$pageTitle )); ?>
    <h3>
        <?php echo $pageTitle; ?> <small><em><?php echo $oQuestion->title;?></em> (ID: <?php echo $oQuestion->qid;?>)</small>
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
                                                <?php $this->renderPartial('/admin/survey/Question/subquestionsAndAnswers/_subquestion', array(
                                                    'position'  => $position,
                                                    'scale_id'  => $scale_id,
                                                    'activated' => $activated,
                                                    'first'     => $first,
                                                    'surveyid'  => $surveyid,
                                                    'gid'       => $gid,
                                                    'qid'       => $row->qid,
                                                    'language'  => $row->language,
                                                    'title'     => $row->title,
                                                    'question'  => $row->question,
                                                    'relevance' => $row->relevance,
                                                    'oldCode'   => true,
                                                ));?>

                                            <?php elseif($viewType=='answerOptions'):?>
                                                <?php $this->renderPartial('/admin/survey/Question/subquestionsAndAnswers/_answer_option', array(
                                                    'position'          => $position,
                                                    'first'             => $first,
                                                    'assessmentvisible' => $assessmentvisible,
                                                    'scale_id'          => $scale_id,
                                                    'title'             => $row->code,
                                                    'surveyid'          => $surveyid,
                                                    'gid'               => $gid,
                                                    'qid'               => $qid,
                                                    'language'          => $row->language,
                                                    'assessment_value'  => $row->assessment_value,
                                                    'sortorder'         => $row->sortorder,
                                                    'answer'            => $row->answer,
                                                    'oldCode'   => true,
                                                ));?>

                                            <?php endif; ?>

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

                                    <button <?php echo $disabled; ?>  id='btnquickadd_<?php echo $anslang; ?>_<?php echo $scale_id; ?>' data-scale-id="<?php echo $scale_id; ?>" class='btn btn-default btnquickadd' type='button'  data-toggle="modal" data-target="#quickaddModal" data-scale-id="<?php echo $scale_id; ?>">
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
                        <!-- For javascript -->
                        <input
                            type="hidden"
                            id="add-input-javascript-datas"
                            data-url="<?php echo App()->createUrl('/admin/questions/sa/getSubquestionRowForAllLanguages/');?>"
                            data-quickurl="<?php echo App()->createUrl('/admin/questions/sa/getSubquestionRowQuickAdd/');?>"
                            data-errormessage="An error occured while processing the ajax request."
                            data-surveyid="<?php echo $surveyid;?>"
                            data-gid="<?php echo $gid;?>"
                            data-qid="<?php echo $qid;?>"
                            data-scale-id="<?php echo $scale_id-1; // -1 : because it's incremented via <  ?>"
                        />
                    </p>

                </div>
                <input type='hidden' id='bFullPOST' name='bFullPOST' value='1' />

            </form>
        </div>
    </div>
</div>
