<?php
/**
 * List of all installed question themes
 * @var QuestionTheme $oQuestionTheme
 */

?>

<?php
require_once Yii::getPathOfAlias('application.extensions.admin.grid.FloatingActionsWidget.actions.QuestionThemeMassiveActions') . '.php';
$aFloatingActions = \actions\QuestionThemeMassiveActions::getActions();

$this->widget('application.extensions.admin.grid.CLSGridView', [
    'dataProvider'          => $oQuestionTheme->search(),
    'filter'                => $oQuestionTheme,
    'id'                    => 'questionthemes-grid',
    'lsCaption'             => gT('Question themes'),
    'lsPageSizeCurrentValue'  => $pageSize,
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
App()->getClientScript()->registerScriptFile(
    Yii::app()->getAssetManager()->publish('assets/scripts/admin/installedThemesList.js'
    )
);

