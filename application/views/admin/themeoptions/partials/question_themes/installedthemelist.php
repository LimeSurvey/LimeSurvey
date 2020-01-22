<?php
/**
 * @var QuestionTheme $oQuestionTheme
 */
?>
<div class="row">
    <div class="col-sm-12 content-right">
        <?php
        $massiveAction = App()->getController()->renderPartial(
            '/admin/themeoptions/massive_action/_selector',
            array(
                'oQuestionTheme' => $oQuestionTheme,
                'gridID'         => 'questionthemes-grid',
                'dropupID'       => 'questionsthemes-dropup',
                'pk'             => 'questionId'
            ),
            true,
            false);

        $this->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider'    => $oQuestionTheme->search(),
            'filter'          => $oQuestionTheme,
            'id'              => 'questionthemes-grid',
            'summaryText'     => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                    "<div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div>",
                    CHtml::dropDownList('pageSize', $pageSize, App()->params['pageSizeOptions'], array('class' => 'changePageSize form-control', 'style' => 'display: inline; width: auto'))
                ),
            'columns'         => array(
                array(
                    'id'             => 'questionId',
                    'class'          => 'CCheckBoxColumn',
                    'selectableRows' => '100',
                ),
//                    array(
//                        'header' => gT('Preview'),
//                        'name' => 'preview',
//                        'value'=> '$data->preview',
//                        'type'=>'raw',
//                        'htmlOptions' => array('class' => 'col-md-1'),
//                        'filter' => false,
//                    ),

                array(
                    'header'      => gT('Name'),
                    'name'        => 'name',
                    'value'       => '$data->name',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header'      => gT('Description'),
                    'name'        => 'description',
                    'value'       => '$data->description',
                    'htmlOptions' => array('class' => 'col-md-3'),
                    'type'        => 'raw',
                ),

                array(
                    'header'      => gT('Type'),
                    'name'        => 'core_theme',
                    'value'       => '($data->core_theme == 1) ? gT("Core Theme") : gT("User Theme")',
                    'type'        => 'raw',
                    'htmlOptions' => array('class' => 'col-md-2'),
                    "filter"      => array(1 => gT("Core Theme"), 0 => gT('User Theme'))
                ),

                array(
                    'header'      => gT('Extends'),
                    'name'        => 'extends',
                    'value'       => '$data->extends',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),
                array(
                    'header'            => gT('Visibility'),
                    'headerHtmlOptions' => ['title' => gT('Visible inside the Question Selector')],
                    'name'              => 'visible',
                    'value'             => '$data->getVisibilityButton()',
                    'type'              => 'raw',
                    'htmlOptions'       => array('class' => 'col-md-1'),
                    "filter"            => array('N' => gT("Off"), 'Y' => gT('On')),
                )
            ),
            'ajaxUpdate'      => 'questionthemes-grid',
            'ajaxType'        => 'POST',
            // todo create a new javascript file and call function from here, related: 1573120573738
            'afterAjaxUpdate' => '
                                function(id, data){
                                    window.LS.doToolTip();
                                    bindListItemclick();
                                    $(".toggle_question_theme").each(function(){
                                        $(this).bootstrapSwitch();
                                    });
                                    $(".toggle_question_theme").on("switchChange.bootstrapSwitch", function(event, state) {
                                        $url = $(this).attr("data-url");
                                        $.ajax({
                                            url : $url,
                                            type : "GET",
                                            dataType : "html",
                                
                                            // html contains the buttons
                                            success : function(html, statut){
                                            }
                                        });
                                    });
                                }',
        ));
        ?>

        <?php
        // todo create a new javascript file and call function from here, related: 1573120573738
        $script = '
                jQuery(document).on("change", "#pageSize", function(){
                    $.fn.yiiGridView.update("questionthemes-grid",{ data:{ pageSize: $(this).val() }});
                });
                $(".toggle_question_theme").each(function(){
                    $(this).bootstrapSwitch();
                });
                $(".toggle_question_theme").on("switchChange.bootstrapSwitch", function(event, state) {
                    $url = $(this).attr("data-url");
                    $.ajax({
                        url : $url,
                        type : "GET",
                        dataType : "html",
            
                        // html contains the buttons
                        success : function(html, statut){
                        }
                    });
                });
                ';
        App()->getClientScript()->registerScript('questionthemes-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);
        ?>

    </div>
</div>
