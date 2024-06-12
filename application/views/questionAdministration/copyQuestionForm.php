<?php

/** @var QuestionAdministrationController $this */
/** @var Survey $oSurvey */
/** @var Question $oQuestion*/
/** @var QuestionGroup $oQuestionGroup */
/** @var string $renderSpecificTopbar */

?>

<div id='edit-question-body' class='side-body'>

    <?= $jsVariablesHtml; ?>

    <!-- Page Title-->
    <div class="pagetitle h1">
        <?php
        eT("Copy question");
        ?>
    </div>

    <div id="copy-question" class="row">
        <!-- Form for the whole page-->
        <?php echo CHtml::form(array("questionAdministration/copyQuestion"), 'post',
            array('class' => 'form30 ', 'id' => 'form_copy_question', 'name' => 'frmeditquestion')); ?>
        <?php /** this btn is trigger by save&close topbar button in copyQuestiontobar_view  */ ?>
        <input type="hidden" name="sid" value="<?= $oSurvey->sid; ?>" />
        <input
                type='submit'
                style="display:none"
                class="btn navbar-btn button white btn-primary"
                id = 'submit-copy-question'
                name="savecopy"
                value='<?php eT("Copy question"); ?>'
        />
        <!-- The tabs & tab-fanes -->
        <div class="row">
            <div class="col-xl-8 pe-1">
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
            <div class="col-xl-4 settings-accordion-container" id="accordion-container">
                <?php // TODO : find why the $groups can't be generated from controller?>
                <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
                    <!-- Copy options -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" role="tab" id="heading-copy">
                            <button
                                class="accordion-button selector--questionEdit-collapse"
                                type="button"
                                role="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-copy"
                                aria-expanded="false"
                                aria-controls="collapse-copy"
                            >
                                <?php eT("Copy options"); ?>
                            </button>
                        </h2>
                        <div id="collapse-copy" class="accordion-collapse collapse show" role="tabpanel" aria-labelledby="heading-copy">
                            <div class="accordion-body">
                                <div class="mb-3">
                                    <label class=" form-label" for='copysubquestions'><?php eT("Copy subquestions?"); ?></label>
                                    <div>
                                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                            'name'          => 'copysubquestions',
                                            'checkedOption' => '1',
                                            'selectOptions' => [
                                                '1' => gT('Yes'),
                                                '0' => gT('No'),
                                            ],
                                        ]); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class=" form-label" for='copyanswers'><?php eT("Copy answer options?"); ?></label>
                                    <div>
                                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                            'name'          => 'copyanswers',
                                            'checkedOption' => '1',
                                            'selectOptions' => [
                                                '1' => gT('Yes'),
                                                '0' => gT('No'),
                                            ],
                                        ]); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class=" form-label" for='copydefaultanswers'><?php eT("Copy default answers?"); ?></label>
                                    <div>
                                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                            'name'          => 'copydefaultanswers',
                                            'checkedOption' => '1',
                                            'selectOptions' => [
                                                '1' => gT('Yes'),
                                                '0' => gT('No'),
                                            ],
                                        ]); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for='copyattributes'>
                                        <?php eT("Copy question settings?"); ?>
                                    </label>
                                    <div class="">
                                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                            'name'          => 'copyattributes',
                                            'checkedOption' => '1',
                                            'selectOptions' => [
                                                '1' => gT('Yes'),
                                                '0' => gT('No'),
                                            ],
                                        ]); ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class=" form-label" for='gid'><?php eT("Question group:"); ?></label>
                                    <div class="">
                                        <select name='gid' id='gid' class="form-select" >
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
