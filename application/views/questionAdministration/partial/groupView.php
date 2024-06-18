<?php

/**
 * This file render the list of groups
 * It use the QuestionGroup model search method to build the data provider.
 *
 * @var $groupModel  QuestionGroup    the QuestionGroup model
 * @var $surveyid int
 * @var $surveybar array
 * @var $oSurvey Survey
 *
 */
?>
<?php $pageSize = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']); ?>

<div class='side-body'>
    <div class="row">


        <div class="">
            <?php echo $this->renderPartial(
                'partial/topbarBtns/listquestiongroupsTopbarLeft_view',
                [
                    'oSurvey' => $oSurvey,
                    'hasSurveyContentCreatePermission' => $hasSurveyContentCreatePermission
                ],
                true
            );
            ?>

            <div class="mt-4">
                <?php App()->getController()->renderPartial(
                    '/admin/survey/surveybar_addgroupquestion', //todo this view must be moved to correct position
                    [
                        'surveybar' => $surveybar,
                        'oSurvey' => $oSurvey,
                        'surveyHasGroup' => isset($oSurvey->groups) ? $oSurvey->groups : false
                    ]
                ); ?>
            </div>

            <!-- Search Box -->
            <?php $form = $this->beginWidget('TbActiveForm', array(
                'action' => App()->createUrl(
                    'questionAdministration/listquestions',
                    [
                        'surveyid' => $oSurvey->primaryKey,
                        'activeTab' => 'groups'
                    ]
                ),
                'method' => 'get',
                'htmlOptions' => array(
                    'class' => '',
                ),
            )); ?>
            <div class="row row-cols-lg-auto g-1 align-items-center mb-3 float-end">
                <div class="col-12">
                    <?php echo CHtml::label(gT('Search by group name:'), 'group_name', array('class' => 'text-nowrap col-sm-7 col-form-label col-form-label-sm')); ?>
                </div>
                <div class="col-12">
                    <?php echo $form->textField($groupModel, 'group_name', array('class' => 'form-control')); ?>
                </div>


                <div class="col-12">
                    <?php echo CHtml::submitButton(gT('Search', 'unescaped'), array('class' => 'btn btn-primary')); ?>
                    <a href="<?php echo Yii::app()->createUrl(
                                    'questionAdministration/listQuestions',
                                    [
                                        'surveyid' => $oSurvey->primaryKey,
                                        'activeTab' => 'groups'
                                    ]
                                ); ?>" class="btn btn-warning">
                        <span class="ri-refresh-line"></span>
                        <?php eT('Reset'); ?>
                    </a>
                </div>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
    <hr />
    <!-- The table grid  -->
    <div class="row ls-space margin">
        <?php
        $this->widget(
            'ext.admin.grid.CLSGridView', //done
            [
                'id'              => 'question-group-grid',
                'dataProvider'    => $groupModel->search(),
                'emptyText'       => gT('No question groups found.'),
                'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                    gT('%s rows per page'),
                    CHtml::dropDownList(
                        'pageSize',
                        $pageSize,
                        Yii::app()->params['pageSizeOptions'],
                        [
                            'class' => 'changePageSize form-select',
                            'style' => 'display: inline; width: auto'
                        ]
                    )
                ),

                // Columns to dispplay
                'columns'         => [
                    // Group Id
                    [
                        'header' => gT('Group ID'),
                        'name'   => 'group_id',
                        'value'  => '$data->gid',
                        'filter'            => false,

                    ],

                    // Group Order
                    [
                        'header' => gT('Group order'),
                        'name'   => 'group_order',
                        'value'  => '$data->group_order',
                        'filter'            => false,
                    ],

                    // Group Name
                    [
                        'header'      => gT('Group name'),
                        'name'        => 'group_name',
                        'value'       => '$data->primaryTitle',
                        'htmlOptions' => ['class' => ''],
                    ],

                    // Description
                    [
                        'header'      => gT('Description'),
                        'name'        => 'description',
                        'type'        => 'raw',
                        'value'       => 'viewHelper::flatEllipsizeText($data->primaryDescription, true, 0)',
                        'htmlOptions' => ['class' => ''],
                        'filter'            => false,
                    ],
                    // Action buttons (defined in model)
                    [
                        'header'      => gT('Action'),
                        'name'        => 'actions',
                        'type'        => 'raw',
                        'filter'            => false,
                        'value'       => '$data->buttons',
                        'headerHtmlOptions' => ['class' => 'ls-sticky-column'],
                        'htmlOptions'       => ['class' => 'text-center button-column ls-sticky-column'],
                    ],

                ],
                'ajaxUpdate'      => 'question-group-grid',
                'afterAjaxUpdate' => 'bindPageSizeChange'
            ]
        );
        ?>
    </div>
</div>

<!-- To update rows per page via ajax -->
<?php
$paginationAndTabActive = <<<JAVASCRIPT

var bindPageSizeChange = function () {
  $(document).trigger("actions-updated");
};

const activeTabContent = () => {
  const params = new URLSearchParams(window.location.search);
  const activeTab = params.get("activeTab");
  if (activeTab) {
    // set tab as active
    const selector = '#overviewTab a[href="' + "#" + activeTab + '"' + "]";
    const tab = document.querySelector(selector);
    if (tab) {
      tab.classList.add("active");
      const tabs = document.querySelectorAll("#overviewTab a.nav-link");
      for (const tabElement of tabs) {
        if (tabElement !== tab) {
          tabElement.classList.remove("active");
        }
      }
    }
    // show tab content
    const tabContents = document.querySelectorAll(".tab-content .tab-pane");
    for (const tabContent of tabContents) {
      if (tabContent.id === activeTab) {
        tabContent.classList.add("active");
      } else {
        tabContent.classList.remove("active");
      }
    }
  }
};
JAVASCRIPT;

App()->getClientScript()->registerScript("ListQuestionGroups-pagination-active-tab", $paginationAndTabActive, LSYii_ClientScript::POS_BEGIN); ?>

<?php App()->getClientScript()->registerScript("ListQuestionGroups-run-pagination", "bindPageSizeChange(); activeTabContent();", LSYii_ClientScript::POS_POSTSCRIPT); ?>