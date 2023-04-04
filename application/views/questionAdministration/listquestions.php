<?php
/**
 * This file render the list of groups
 */

/** @var QuestionAdministrationController $this */
/** @var Survey $oSurvey */
/** @var Question $model */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyListQuestions');
$baseLanguage = $oSurvey->language;
?>
<?php $pageSize = App()->user->getState('pageSize', App()->params['defaultPageSize']); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3 class="ls-flex ls-flex-row">
        <?php if (App()->request->getParam('group_name') != ''): ?>
            <div class="ls-flex-item text-start"><?php eT('Questions in group: '); ?> <em><?php echo \CHtml::encode(App()->request->getParam('group_name')); ?></em></div>
        <?php else: ?>
            <div class="ls-flex-item text-start"><?php eT('Questions in this survey'); ?></div>
        <?php endif;?>
    </h3>


    <div class="row">
        <div class="col-12 content-right">

            <!-- Search Box -->
            <div class="row">
                <div class="col-12 ls-flex ls-flex-row">
                    <div class="ls-flex-item text-start">
                        <?php App()->getController()->renderPartial(
                            '/admin/survey/surveybar_addgroupquestion',
                            [
                                'surveybar' => $surveybar,
                                'oSurvey' => $oSurvey,
                                'surveyHasGroup' => isset($surveyHasGroup) ? $surveyHasGroup : false
                            ]
                        ); ?>
                    </div>

                    <!-- Begin Form -->
                    <?php
                    $form = $this->beginWidget('CActiveForm', array(
                        'action' => App()->createUrl(
                            'questionAdministration/listquestions',
                            ['surveyid' => $oSurvey->primaryKey]
                        ),
                        'method' => 'get',
                        'htmlOptions' => array(
                            'class' => '',
                        ),
                    )); ?>
                    <div class="row row-cols-lg-auto g-1 align-items-center mb-3 float-end">
                        <!-- Search input -->
                        <div class="col-12">
                            <?php
                            echo $form->label(
                                $model,
                                'search',
                                array('label' => gT('Search:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
                            ); ?>
                        </div>
                        <div class="col-12">
                            <?php
                            echo $form->textField($model, 'title', array('class' => 'form-control')); ?>
                        </div>

                        <!-- Select group -->
                        <div class="col-12">
                            <?php
                            echo $form->label(
                                $model,
                                'group',
                                array('label' => gT('Group:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
                            ); ?>
                        </div>
                        <div class="col-12">
                            <select name="gid" class="form-select">
                                <option value=""><?php eT('(Any group)'); ?></option>
                                <?php foreach ($oSurvey->groups as $group): ?>
                                    <option value="<?php echo $group->gid; ?>" <?php if ($group->gid == $model->gid) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo flattenText($group->questiongroupl10ns[$oSurvey->language]->group_name); ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <?php
                            echo CHtml::submitButton(
                                gT('Search', 'unescaped'),
                                ['class' => 'btn btn-primary']
                            ); ?>
                            <a href="<?php
                            echo App()->createUrl(
                                'questionAdministration/listquestions',
                                ['surveyid' => $oSurvey->primaryKey]
                            ); ?>" class="btn btn-warning">
                                <span class="ri-refresh-line"></span>
                                <?php
                                eT('Reset'); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                    $this->endWidget(); ?>
                   <!-- form -->
                </div>
            </div>
            <hr/>
            <!-- Grid -->
            <div class="row ls-space margin top-10">
                <div class="col-12">
                    <?php
                    $massiveAction = Yii::app()->getController()->renderPartial(
                        '/admin/survey/Question/massive_actions/_selector',
                        array('model' => $model, 'oSurvey' => $oSurvey),
                        true,
                        false
                    );
                    $this->widget('ext.admin.grid.CLSGridView', array( //done
                        'dataProvider' => $model->search(),
                        'id' => 'question-grid',
                        'emptyText' => gT('No questions found.'),
                        'massiveActionTemplate' => $massiveAction,
                        'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                            . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSize',
                                    $pageSize,
                                    App()->params['pageSizeOptions'],
                                    array(
                                        'class' => 'changePageSize form-select',
                                        'style' => 'display: inline; width: auto'
                                    )
                                )
                            ),
                        'columns' => $model->questionListColumns,
                        'ajaxUpdate' => 'question-grid',
                        'afterAjaxUpdate' => "bindPageSizeChange"
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="question-preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"><?php eT("Question preview"); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="frame-question-preview" src="" style="zoom:0.60" width="99.6%" height="600" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close");?></button>
            </div>
        </div>
    </div>
</div>

<!-- To update rows per page via ajax -->
<?php App()->getClientScript()->registerScript("ListQuestions-pagination",
    "
        var bindPageSizeChange = function(){
            $(document).trigger('actions-updated');
        };
    ",
    LSYii_ClientScript::POS_BEGIN
); ?>

<?php App()->getClientScript()->registerScript("ListQuestions-run-pagination", "bindPageSizeChange(); ", LSYii_ClientScript::POS_POSTSCRIPT); ?>
