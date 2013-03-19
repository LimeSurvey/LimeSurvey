<div style="width: 50%; margin: auto;">
<?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    $dataProvider = new CArrayDataProvider($data);
    
    $gridColumns = array(
        array(// display the 'name' attribute
            'class' => 'CLinkColumn',
            'header' => 'Plugin',
            'labelExpression' => function($data) { return $data['name']; },
            'urlExpression' => function($data) { return array("/plugins/configure", "id" => $data['id']); }    
        ),
        array(// display the activation link
            'class' => 'CLinkColumn',
            'header' => 'Activation status',
            'labelExpression' => function($data) { return ($data['active'] == 0 ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', 'Activate', array('width' => 16, 'height' => 16)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', 'Deactivate', array('width' => 16, 'height' => 16))); },
            'urlExpression' => function($data) { return $data['active'] == 0 ? array("/plugins/activate", "id" => $data['id']) : array("/plugins/activate", "id" => $data['id']); }    
        )
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
    ));
?>
</div>