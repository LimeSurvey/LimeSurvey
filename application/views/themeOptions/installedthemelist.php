<?php
/**
 * List of all installed question themes
 * @var QuestionTheme $oQuestionTheme
 */

?>

<?php
$aFloatingActions = require(__DIR__ . '/floatingActions/_questionThemeActions.php');

$this->widget('application.extensions.admin.grid.CLSGridView', [
    'dataProvider'          => $oQuestionTheme->search(),
    'filter'                => $oQuestionTheme,
    'id'                    => 'questionthemes-grid',
    'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
            gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $pageSize,
                App()->params['pageSizeOptions'],
                [
                    'id' => 'questionthemes-pageSize',
                    'class' => 'changePageSize form-select',
                    'style' => 'display: inline; width: auto',
                    'aria-label' => gT('Rows per page')
                ]
            )
        ),
    'columns'               => [
        [
            'id'             => 'questionId',
            'class'          => 'CCheckBoxColumn',
            'selectableRows' => '100',
            'checkBoxHtmlOptions' => ['class' => 'massiveActionsCheckbox'],
        ],

        [
            'header'      => gT('Name'),
            'name'        => 'name',
            'value'       => '$data->name',
            'htmlOptions' => ['class' => 'col-lg-2'],

        ],

        [
            'header'      => gT('Description'),
            'name'        => 'description',
            'value'       => '$data->description',
            'htmlOptions' => ['class' => 'col-lg-3'],
        ],

        [
            'header'      => gT('Type'),
            'name'        => 'core_theme',
            'value'       => '($data->core_theme == 1) ? gT("Core theme, "unescaped") : gT("User theme, "unescaped")',
            'htmlOptions' => ['class' => 'col-lg-2'],
            "filter"      => [1 => gT("Core theme", "unescaped"), 0 => gT("User theme", "unescaped")]
        ],

        [
            'header'      => gT('Extends'),
            'name'        => 'extends',
            'value'       => '$data->extends',
            'htmlOptions' => ['class' => 'col-lg-2'],
        ],
        [
            'header'            => gT('Visibility'),
            'headerHtmlOptions' => ['title' => gT('Visible inside the question type selector')],
            'name'              => 'visible',
            'value'             => '$data->getVisibilityButton()',
            'type'              => 'raw', // From model HTML directly
            'htmlOptions'       => ['class' => 'col-lg-1'],
            "filter"            => ['N' => gT("Off"), 'Y' => gT('On')],
        ]
    ],
    'showSelectionBar'      => false,
    'ajaxUpdate'            => 'questionthemes-grid',
    'ajaxType'              => 'POST',
    // This will be called FIRST before restoreCheckboxes, so we use lsAfterAjaxUpdate instead
    // But we also register a separate event to ensure the bar is updated after the full pipeline

]);

if (!empty($aFloatingActions)) {
    $this->widget(
        'ext.admin.grid.FloatingActionsWidget.FloatingActionsWidget',
        [
            'pk'       => 'questionId',
            'gridId'   => 'questionthemes-grid',
            'aActions' => $aFloatingActions,
        ]
    );
}
?>

<?php
// todo create a new javascript file and call function from here, related: 1573120573738
$script = '
                jQuery(document).on("change", "#questionthemes-pageSize", function () {
                    $.fn.yiiGridView.update("questionthemes-grid", {
                        data: {
                            pageSize: $(this).val()
                        }
                    });
                });
                // Use event delegation for toggle question themes so it works across pagination
                jQuery(document).on("change", ".toggle_question_theme", function () {
                    let $url = $(this).attr("data-url");
                    let data = new FormData();
                    let xhttp = new XMLHttpRequest();
                    data.append(LS.data.csrfTokenName, LS.data.csrfToken);
                    xhttp.open("POST", $url, true);
                    xhttp.send(data);
                });
                // Ensure floating actions bar is updated whenever checkboxes change  
                jQuery(document).on("change", "#questionthemes-grid tbody .massiveActionsCheckbox", function () {
                    if (window.LS && LS.floatingActions && typeof LS.floatingActions.updateBar === "function") {
                        setTimeout(function() {
                            LS.floatingActions.updateBar("questionthemes-grid", "questionId");
                        }, 50);
                    }
                });
                // Hook into yiiGridView after updates
                jQuery("#questionthemes-grid").on("yiiGridView:afterUpdate", function() {
                    if (window.LS && LS.floatingActions && typeof LS.floatingActions.updateBar === "function") {
                        setTimeout(function() {
                            LS.floatingActions.updateBar("questionthemes-grid", "questionId");
                        }, 100);
                    }
                });
                ';
App()->getClientScript()->registerScript('questionthemes-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);

















