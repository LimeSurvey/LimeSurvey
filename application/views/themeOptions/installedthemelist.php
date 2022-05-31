<?php
/**
 * List of all installed question themes
 * @var QuestionTheme $oQuestionTheme
 */

?>

<?php
$massiveAction = App()->getController()->renderPartial(
    './_selector',
    array(
        'oQuestionTheme' => $oQuestionTheme,
        'gridID' => 'questionthemes-grid',
        'dropupID' => 'questionsthemes-dropup',
        'pk' => 'questionId'
    ),
    true,
    false
);

        $this->widget('application.extensions.admin.grid.CLSGridView', array(
            'dataProvider'    => $oQuestionTheme->search(),
            'filter'          => $oQuestionTheme,
            'id'              => 'questionthemes-grid',
            'massiveActionTemplate' => $massiveAction,
            'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                gT('%s rows per page'),
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    App()->params['pageSizeOptions'],
                    ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                )
            ),
            'columns'         => array(
                array(
                    'id'             => 'questionId',
                    'class'          => 'CCheckBoxColumn',
                    'selectableRows' => '100',
                ),

        array(
            'header' => gT('Name'),
            'name' => 'name',
            'value' => '$data->name',
            'htmlOptions' => array('class' => 'col-lg-2'),

        ),

        array(
            'header' => gT('Description'),
            'name' => 'description',
            'value' => '$data->description',
            'htmlOptions' => array('class' => 'col-lg-3'),
            'type' => 'raw',
        ),

        array(
            'header' => gT('Type'),
            'name' => 'core_theme',
            'value' => '($data->core_theme == 1) ? gT("Core Theme") : gT("User Theme")',
            'type' => 'raw',
            'htmlOptions' => array('class' => 'col-lg-2'),
            "filter" => array(1 => gT("Core Theme"), 0 => gT('User Theme'))
        ),

        array(
            'header' => gT('Extends'),
            'name' => 'extends',
            'value' => '$data->extends',
            'htmlOptions' => array('class' => 'col-lg-2'),
        ),
        array(
            'header' => gT('Visibility'),
            'headerHtmlOptions' => ['title' => gT('Visible inside the Question Selector')],
            'name' => 'visible',
            'value' => '$data->getVisibilityButton()',
            'type' => 'raw',
            'htmlOptions' => array('class' => 'col-lg-1'),
            "filter" => array('N' => gT("Off"), 'Y' => gT('On')),
        )
    ),
    'ajaxUpdate' => 'questionthemes-grid',
    'ajaxType' => 'POST',
    // @todo create a new javascript file and call function from here, related: 1573120573738
    'afterAjaxUpdate' => '
                                function(id, data){
                                    window.LS.doToolTip();
                                    bindListItemclick();
                                    let togglequestionthemes = document.getElementsByClassName("toggle_question_theme");
                                    for (let togglequestiontheme of togglequestionthemes) {
                                        togglequestiontheme.addEventListener("change", () => {
                                            let $url = togglequestiontheme.getAttribute("data-url");
                                            let xhttp = new XMLHttpRequest();
                                            xhttp.open("GET", $url, true);
                                            xhttp.send();
                                        });
                                    }
                                }',
));
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
                        let xhttp = new XMLHttpRequest();
                        xhttp.open("GET", $url, true);
                        xhttp.send();
                    });
                }
                ';
App()->getClientScript()->registerScript('questionthemes-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);
?>

