<?php $pageSize = App()->user->getState('pageSize', App()->params['defaultPageSize']); ?>



<div class="col-12 content-right">
    <?php echo $this->renderPartial(
                'partial/topbarBtns/listquestionsTopbarLeft_view',
                [
                    'oSurvey' => $oSurvey,
                    'hasSurveyContentCreatePermission' => $hasSurveyContentCreatePermission
                ],
                true
            );
         ?>

    <!-- Search Box -->
    <div class="row mt-4">
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
                        $questionModel,
                        'search',
                        array('label' => gT('Search:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
                    ); ?>
                </div>
                <div class="col-12">
                    <?php
                    echo $form->textField($questionModel, 'title', array('class' => 'form-control')); ?>
                </div>

                <!-- Select group -->
                <div class="col-12">
                    <?php
                    echo $form->label(
                        $questionModel,
                        'group',
                        array('label' => gT('Group:'), 'class' => 'col-sm-3 col-form-label col-form-label-sm')
                    ); ?>
                </div>
                <div class="col-12">
                    <select name="gid" class="form-select">
                        <option value=""><?php eT('(Any group)'); ?></option>
                        <?php foreach ($oSurvey->groups as $group) : ?>
                            <option value="<?php echo $group->gid; ?>" <?php if ($group->gid == $questionModel->gid) {
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
    <hr />
    <!-- Grid -->
    <div class="row ls-space margin top-10">
        <div class="col-12">
            <?php
            $massiveAction = Yii::app()->getController()->renderPartial(
                '/admin/survey/Question/massive_actions/_selector',
                array('model' => $questionModel, 'oSurvey' => $oSurvey),
                true,
                false
            );
            $this->widget('ext.admin.grid.CLSGridView', array( //done
                'dataProvider' => $questionModel->search(),
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
                'columns' => $questionModel->questionListColumns,
                'ajaxUpdate' => 'question-grid',
                'afterAjaxUpdate' => "bindPageSizeChange"
            ));
            ?>
        </div>
    </div>
</div>


<!-- To update rows per page via ajax -->
<?php App()->getClientScript()->registerScript(
    "ListQuestions-pagination",
    "
        var bindPageSizeChange = function(){
            $(document).trigger('actions-updated');
        };
    ",
    LSYii_ClientScript::POS_BEGIN
); ?>

<?php App()->getClientScript()->registerScript("ListQuestions-run-pagination", "bindPageSizeChange(); ", LSYii_ClientScript::POS_POSTSCRIPT); ?>