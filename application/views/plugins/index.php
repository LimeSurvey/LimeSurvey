<div class="col-md-8 col-md-offset-2">
<?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    
    $gridColumns = array(
        array(// display the activation link
            'class' => 'CLinkColumn',
            'header' => gT('Status'),
            'labelExpression' => function(\ls\pluginmanager\PluginConfig $config) { return ($config->active ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Active'), array('width' => 32, 'height' => 32)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Inactive'), array('width' => 32, 'height' => 32))); },
            'url' => '#'
        ),
        array(// display the activation link
            'class' => 'CDataColumn',
            'type' => 'raw',
            'header' => gT('Action'),
            'value' => function(\ls\pluginmanager\PluginConfig $data) {
                if (!$data->active) { 
                    $output = CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Activate'), array('width' => 16, 'height' => 16)), array("/plugins/activate", "id" => $data['id']));
                } else {
                    $output = CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Deactivate'), array('width' => 16, 'height' => 16)), array("/plugins/deactivate", "id" => $data['id'])); 
                    if ($data->getPlugin() != null && count($data->getPlugin()->getPluginSettings()) > 0) {
                        $output .= CHtml::link(CHtml::image(App()->getConfig('adminimageurl') . 'survey_settings_30.png', gT('Configure'), array('width' => 16, 'height' => 16, 'style' => 'margin-left: 8px;')), array("/plugins/configure", "id" => $data['id'])); 
                    }
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
        [
            'type' => 'raw',
            'header' => gT('Errors in limesurvey.json'),
            'value' => function($pluginConfig) {
                $result = '<dl>';
                $pluginConfig->validate();
                foreach($pluginConfig->errors as $field => $errors) {
                    $result .= CHtml::tag('dt', [], $field);
                    $result .= CHtml::tag('dd', [], $errors[0]);
                }
                $result .= '</dl>';
                return $result;
            }
        ],
    ); 
        
        /*
            array(            // display a column with "view", "update" and "delete" buttons
            'class' => 'CallbackColumn',
            'label' => function($data) { return ($data->active == 1) ? "deactivate": "activate"; },
            'url' => function($data) { return array("/plugins/activate", "id"=>$data["id"]); }
        )
    );
        */
    $this->widget('TbGridView', array(
        'dataProvider'=> $plugins,
        'columns'=>$gridColumns,
//        'rowCssClassExpression'=> function ($index, $data) { return ($index % 2 ? 'even' : 'odd') . ' ' . ($data['new']==1 ? "new" : "old"); },
//        'itemsCssClass' => 'items table-condensed table-bordered'
    ));

?>
</div>
<div class="col-md-6 col-md-offset-3">
<?php
    echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['plugins/configureAuth'], 'post');
    echo TbHtml::dropDownListControlGroup('authorizationPlugin', SettingGlobal::get('authorizationPlugin', null), TbHtml::listData($authorizers, 'id', 'name'), [
        'label' => 'Authorization plugin:',
        'labelOptions' => ['class' => 'col-md-6'],
        'controlOptions' => ['class' => 'col-md-6'],
    ]);
    echo TbHtml::checkBoxListControlGroup('authenticationPlugins', SettingGlobal::get('authenticationPlugins', []), TbHtml::listData($authenticators, 'id', 'name'), [
        'label' => 'Authentication plugins:',
        'labelOptions' => ['class' => 'col-md-6'],
        'controlOptions' => ['class' => 'col-md-6'],
    ]);
    echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
    echo TbHtml::submitButton(gT('Save'), ['color' => 'primary']);
    echo TbHtml::closeTag('div');
    echo TbHtml::endForm();
    
    var_dump($modules);
?>
    
</div>