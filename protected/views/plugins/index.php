<div class="col-md-12">
<?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    
    $gridColumns = [
        [// display the activation link
            'class' => 'CLinkColumn',
            'header' => gT('Status'),
            'labelExpression' => function(\ls\pluginmanager\PluginConfig $config) { return ($config->active ? CHtml::image(App()->getTheme()->baseUrl . '/images/active.png', gT('Active'), ['width' => 32, 'height' => 32]) : CHtml::image(App()->getTheme()->baseUrl . '/images/inactive.png', gT('Inactive'), ['width' => 32, 'height' => 32])); },
            'url' => '#'
        ],
        [// display the activation link
            'class' => 'CDataColumn',
            'type' => 'raw',
            'header' => gT('Action'),
            'value' => function(\ls\pluginmanager\PluginConfig $data) {
                if (!$data->active) {

                    $output = CHtml::link(CHtml::image(App()->getTheme()->baseUrl . '/images/active.png', gT('Activate'), ['width' => 16, 'height' => 16]), ["/plugins/activate", "id" => $data['id']]);
                } else {
                    $output = CHtml::link(CHtml::image(App()->getTheme()->baseUrl . '/images/inactive.png', gT('Deactivate'), ['width' => 16, 'height' => 16]), ["/plugins/deactivate", "id" => $data['id']]);
                    if ($data->getPlugin() != null && count($data->getPlugin()->getPluginSettings()) > 0) {
                        $output .= CHtml::link(App()->getTheme()->baseUrl . '/images/survey_settings_30.png', gT('Configure'), ['width' => 16, 'height' => 16, 'style' => 'margin-left: 8px;'], ["/plugins/configure", "id" => $data['id']]);
                    }
                }
                return $output;
            }
        ],
        [// display the 'name' attribute
            'class' => 'CDataColumn',
            'header' => gT('Plugin'),
            'name' => 'name'
        ],
        [// display the 'name' attribute
            'class' => 'CDataColumn',
            'header' => gT('Description'),
            'name' => 'description'
        ],
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
    ];
        
        /*
            array(            // display a column with "view", "update" and "delete" buttons
            'class' => 'CallbackColumn',
            'label' => function($data) { return ($data->active == 1) ? "deactivate": "activate"; },
            'url' => function($data) { return array("/plugins/activate", "id"=>$data["id"]); }
        )
    );
        */
    $this->widget('TbGridView', [
        'dataProvider'=> $plugins,
        'columns'=>$gridColumns,
//        'rowCssClassExpression'=> function ($index, $data) { return ($index % 2 ? 'even' : 'odd') . ' ' . ($data['new']==1 ? "new" : "old"); },
//        'itemsCssClass' => 'items table-condensed table-bordered'
    ]);

?>
</div>
<div class="col-md-12">

<?php
    echo TbHtml::well(
        gT('Configure global authentication and authorization settings below.') .
        gT('Multiple authentication plugins can be active at the same time.') .
        gT('Only one authorization plugin can be active at any time.')

    );
    echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['plugins/configureAuth'], 'post');
    echo TbHtml::dropDownListControlGroup('authorizationPlugin', SettingGlobal::get('authorizationPlugin', null), TbHtml::listData($authorizers, 'id', 'name'), [
        'label' => gT('Authorization plugin'),
        'help' => gT('Authorization is the process of deciding if a user is allowed to do what he or she is attempting to do.'),
        'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
        'labelOptions' => ['class' => 'col-md-6'],
        'controlOptions' => ['class' => 'col-md-6'],
    ]);
    echo TbHtml::checkBoxListControlGroup('authenticationPlugins', SettingGlobal::get('authenticationPlugins', []), TbHtml::listData($authenticators, 'id', 'name'), [
        'label' => gT('Authentication plugins'),
        'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
        'labelOptions' => ['class' => 'col-md-6'],
        'controlOptions' => ['class' => 'col-md-6'],
        'help' => gT('Authorization is the process of deciding if the user is who he or she claims to be.'),
    ]);
    echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
    echo TbHtml::submitButton(gT('Save'), ['color' => 'primary']);
    echo TbHtml::closeTag('div');
    echo TbHtml::endForm();

?>
    
</div>