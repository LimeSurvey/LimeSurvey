<?php
/**
 * List of all installed question themes
 * @var QuestionTheme $oQuestionTheme
 */

?>

<?php
$massiveAction = App()->getController()->renderPartial(
    './_selector',
    [
        'oQuestionTheme' => $oQuestionTheme,
        'gridID'         => 'questionthemes-grid',
        'dropupID'       => 'questionsthemes-dropup',
        'pk'             => 'questionId'
    ],
    true,
    false
);

$this->widget('application.extensions.admin.grid.CLSGridView', [
    'dataProvider'          => $oQuestionTheme->search(),
    'filter'                => $oQuestionTheme,
    'id'                    => 'questionthemes-grid',
    'massiveActionTemplate' => $massiveAction,
    'summaryText'           => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
            gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $pageSize,
                App()->params['pageSizeOptions'],
                ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
            )
        ),
    'columns'               => [
        [
            'id'             => 'questionId',
            'class'          => 'CCheckBoxColumn',
            'selectableRows' => '100',
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
            'type'        => 'raw',
        ],

        [
            'header'      => gT('Type'),
            'name'        => 'core_theme',
            'value'       => '($data->core_theme == 1) ? gT("Core theme") : gT("User theme")',
            'type'        => 'raw',
            'htmlOptions' => ['class' => 'col-lg-2'],
            "filter"      => [1 => gT("Core theme"), 0 => gT('User theme')]
        ],

        [
            'header'      => gT('Extends'),
            'name'        => 'extends',
            'value'       => '$data->extends',
            'htmlOptions' => ['class' => 'col-lg-2'],
        ],
        [
            'header'            => gT('Visibility'),
            'headerHtmlOptions' => ['title' => gT('Visible inside the question selector')],
            'name'              => 'visible',
            'value'             => '$data->getVisibilityButton()',
            'type'              => 'raw',
            'htmlOptions'       => ['class' => 'col-lg-1'],
            "filter"            => ['N' => gT("Off"), 'Y' => gT('On')],
        ]
    ],
    'ajaxUpdate'            => 'questionthemes-grid',
    'ajaxType'              => 'POST',
    // @todo create a new javascript file and call function from here, related: 1573120573738
    'afterAjaxUpdate'       => '
                                function(id, data){
                                    window.LS.doToolTip();
                                    bindListItemclick();
                                    let togglequestionthemes = document.getElementsByClassName("toggle_question_theme");
                                    for (let togglequestiontheme of togglequestionthemes) {
                                        togglequestiontheme.addEventListener("change", () => {
                                            let $url = togglequestiontheme.getAttribute("data-url");
                                            let data = new FormData();
                                            let xhttp = new XMLHttpRequest();
                                            data.append(LS.data.csrfTokenName, LS.data.csrfToken);
                                            xhttp.open("POST", $url, true);
                                            xhttp.send(data);
                                        });
                                    }
                                }',
]);
?>

<?php
// todo create a new javascript file and call function from here, related: 1573120573738
$script = '
                jQuery(document).on("change", "#pageSize", function () {
                    $.fn.yiiGridView.update("questionthemes-grid", {
                        data: {
                            pageSize: $(this).val()
                        }
                    });
                });
                let togglequestionthemes = document.getElementsByClassName("toggle_question_theme");
                for (let togglequestiontheme of togglequestionthemes) {
                    togglequestiontheme.addEventListener("change", () => {
                        let $url = togglequestiontheme.getAttribute("data-url");
                        let data = new FormData();
                        let xhttp = new XMLHttpRequest();
                        data.append(LS.data.csrfTokenName, LS.data.csrfToken);
                        xhttp.open("POST", $url, true);
                        xhttp.send(data);
                    });
                }
                ';
App()->getClientScript()->registerScript('questionthemes-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);
?>

