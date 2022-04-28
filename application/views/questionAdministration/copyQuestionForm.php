<?php

/** @var QuestionAdministrationController $this */
/** @var Survey $oSurvey */
/** @var Question $oQuestion*/
/** @var QuestionGroup $oQuestionGroup */
/** @var string $renderSpecificTopbar */

?>

<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>

    <?= $jsVariablesHtml; ?>

    <!-- Page Title-->
    <div class="pagetitle h3">
        <?php
        eT("Copy question");
        ?>
    </div>

    <div class="row">
        <!-- Form for the whole page-->
        <?php echo CHtml::form(array("questionAdministration/copyQuestion"), 'post',
            array('class' => 'form30 ', 'id' => 'form_copy_question', 'name' => 'frmeditquestion')); ?>
        <?php /** this btn is trigger by save&close topbar button in copyQuestiontobar_view  */ ?>
        <input type="hidden" name="sid" value="<?= $oSurvey->sid; ?>" />
        <input
                type='submit'
                style="display:none"
                class="btn navbar-btn button white btn-success"
                id = 'submit-copy-question'
                name="savecopy"
                value='<?php eT("Copy question"); ?>'
        />
        <!-- The tabs & tab-fanes -->
        <div class="col-sm-12 col-md-7 content-right">
            <?php
            //rendering the language tabs (questioncode, questiontext, questionhelp)
            $this->renderPartial(
                '_copyQuestionTabsLanguages',
                array(
                    'oSurvey' => $oSurvey,
                    'oQuestion' => $oQuestion,
                    'surveyid' => $oSurvey->sid,
                    //'aqresult' => $aqresult,
                )
            ); ?>
        </div>

        <!-- The Accordion -->
        <div class="col-sm-12 col-md-5" id="accordion-container" style="background-color: #fff; z-index: 2;">
            <?php // TODO : find why the $groups can't be generated from controller?>
            <div id='questionbottom'>
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <!-- Copy options -->
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="heading-copy">
                            <a class="panel-title h4 selector--questionEdit-collapse" role="button"
                               data-toggle="collapse" data-parent="#accordion" href="#collapse-copy"
                               aria-expanded="false" aria-controls="collapse-copy">
                                <?php eT("Copy options"); ?>
                            </a>
                        </div>
                        <div id="collapse-copy" class="panel-collapse collapse  in" role="tabpanel"
                             aria-labelledby="heading-copy">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class=" control-label"
                                           for='copysubquestions'><?php eT("Copy subquestions?"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'copysubquestions',
                                            'id' => 'copysubquestions',
                                            'value' => 'Y',
                                            'onLabel' => gT('Yes'),
                                            'offLabel' => gT('No')
                                        ));
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class=" control-label"
                                           for='copyanswers'><?php eT("Copy answer options?"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'copyanswers',
                                            'id' => 'copyanswers',
                                            'value' => 'Y',
                                            'onLabel' => gT('Yes'),
                                            'offLabel' => gT('No')
                                        ));
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class=" control-label"
                                           for='copydefaultanswers'><?php eT("Copy default answers?"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'copydefaultanswers',
                                            'id' => 'copydefaultanswers',
                                            'value' => 'Y',
                                            'onLabel' => gT('Yes'),
                                            'offLabel' => gT('No')
                                        ));
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class=" control-label"
                                           for='copyattributes'><?php eT("Copy question settings?"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'copyattributes',
                                            'id' => 'copyattributes',
                                            'value' => 'Y',
                                            'onLabel' => gT('Yes'),
                                            'offLabel' => gT('No')
                                        ));
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class=" control-label" for='gid'><?php eT("Question group:"); ?></label>
                                    <div class="">
                                        <select name='gid' id='gid' class="form-control" >
                                            <?php echo getGroupList3($oQuestion->gid, $oQuestion->sid); ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Rendering position widget -->
                                <?php $this->widget('ext.admin.survey.question.PositionWidget.PositionWidget',
                                    array(
                                        'display' => 'ajax_form_group',
                                        'oQuestionGroup' => $oQuestionGroup,
                                    ));
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type='hidden' name='surveyId' value='<?php echo $oQuestion->sid; ?>'/>
        <input type='hidden' name='questionGroupId' value='<?php echo $oQuestionGroup->gid; ?>'/>
        <input type='hidden' name='questionId' value='<?php echo $oQuestion->qid; ?>'/>
        </form>

    </div>
</div>
