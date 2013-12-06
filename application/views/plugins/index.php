<div class="header ui-widget-header"><?php eT('Plugins'); ?></div>
<div style="width: 75%; margin: auto;">
<?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    $dataProvider = new CArrayDataProvider($data);
    
    $gridColumns = array(
        array(// display the activation link
            'class' => 'CLinkColumn',
            'header' => gT('Status'),
            'labelExpression' => function($data) { return ($data['active'] == 1 ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Active'), array('width' => 32, 'height' => 32)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Inactive'), array('width' => 32, 'height' => 32))); },
            'url' => '#'
        ),
        array(// display the activation link
            'class' => 'CDataColumn',
            'type' => 'raw',
            'header' => gT('Action'),
            'value' => function($data) {
                if ($data['active'] == 0)
                { 
                    $output = CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Activate'), array('width' => 16, 'height' => 16)), array("/plugins/activate", "id" => $data['id']));
                } else {
                    $output = CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Deactivate'), array('width' => 16, 'height' => 16)), array("/plugins/deactivate", "id" => $data['id'])); 
                }
                if(count($data['settings'])>0)
                {
                    $output .= CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'survey_settings_30.png', gT('Configure'), array('width' => 16, 'height' => 16, 'style' => 'margin-left: 8px;')), array("/plugins/configure", "id" => $data['id'])); 
                }
                return $output;
            }
        ),
        array(// display the 'name' attribute
            'class' => 'CDataColumn',
            'header' => gT('Plugin'),
            'name' => 'name'
        ),
        array(// display the 'name' attribute
            'class' => 'CDataColumn',
            'header' => gT('Description'),
            'name' => 'description'
        ),
    ); 
        
        /*
            array(            // display a column with "view", "update" and "delete" buttons
            'class' => 'CallbackColumn',
            'label' => function($data) { return ($data->active == 1) ? "deactivate": "activate"; },
            'url' => function($data) { return array("/plugins/activate", "id"=>$data["id"]); }
        )
    );
        */
        
    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'dataProvider'=>$dataProvider,
        'columns'=>$gridColumns,
        'rowCssClassExpression'=> function ($data, $row) { return ($row % 2 ? 'even' : 'odd') . ' ' . ($data['new']==1 ? "new" : "old"); },
        'itemsCssClass' => 'items table-condensed table-bordered'
    ));
?>
</div>
