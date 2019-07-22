
<div class="row">
    <div class="col-sm-12 content-right">
        
        <?php 
        $massiveAction = App()->getController()->renderPartial('/admin/themeoptions/massive_action/_selector', array('oSurveyTheme'=>$oSurveyTheme), true, false);
        $this->widget('bootstrap.widgets.TbGridView', array(
            'dataProvider' => $oSurveyTheme->searchGrid(),
            'filter'        => $oSurveyTheme,
            'id'            => 'themeoptions-grid',
            'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                "<div class=\"col-sm-4\" id=\"massive-action-container\">$massiveAction</div>",
                CHtml::dropDownList(
                    'pageSize',
                    $pageSize,
                    Yii::app()->params['pageSizeOptions'],
                    array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
                )          
            ),            
            'columns' => array(
                array(
                    'id'=>'id',
                    'class'=>'CCheckBoxColumn',
                    'selectableRows' => '100',
                ),
                array(
                    'header' => gT('Preview'),
                    'name' => 'preview',
                    'value'=> '$data->preview',
                    'type'=>'raw',
                    'htmlOptions' => array('class' => 'col-md-1'),
                    'filter' => false,
                ),

                array(
                    'header' => gT('Name'),
                    'name' => 'template_name',
                    'value'=>'$data->template_name',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header' => gT('Description'),
                    'name' => 'template_description',
                    'value'=>'$data->template->description',
                    'htmlOptions' => array('class' => 'col-md-3'),
                    'type'=>'raw',
                ),

                array(
                    'header' => gT('Type'),
                    'name' => 'template_type',
                    'value'=>'$data->typeIcon',
                    'type' => 'raw',
                    'htmlOptions' => array('class' => 'col-md-2'),
                    'filter' =>  array('core' => 'Core theme', 'user' => 'User theme'),
                ),

                array(
                    'header' => gT('Extends'),
                    'name' => 'template_extends',
                    'value'=>'$data->template->extends',
                    'htmlOptions' => array('class' => 'col-md-2'),
                ),

                array(
                    'header' => '',
                    'name' => 'actions',
                    'value'=>'$data->buttons',
                    'type'=>'raw',
                    'htmlOptions' => array('class' => 'col-md-1'),
                    'filter' => false,
                ),

            ), 
            'ajaxUpdate'    => true,
            'ajaxType'      => 'POST',
            'afterAjaxUpdate' => 'function(id, data){window.LS.doToolTip();bindListItemclick();}', 
        ));
        ?>

        <!-- To update rows per page via ajax setSession-->
            <?php
            $script = '
                jQuery(document).on("change", "#pageSize", function(){
                    $.fn.yiiGridView.update("themeoptions-grid",{ data:{ pageSize: $(this).val() }});
                });
                ';
            App()->getClientScript()->registerScript('themeoptions-grid', $script, LSYii_ClientScript::POS_POSTSCRIPT);
            ?>

    </div>
</div>
