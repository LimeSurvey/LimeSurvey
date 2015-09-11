<?php
/**
 * This view uses the settings widget
 *
 * TODO do we need the required postfix <font color='red' face='verdana' size='1'> <?php eT("Required"); ?></font> ??
 * TODO Are there new solutions?? What about CHtml::errorSummary($model); in SettingsWidget for error display?
 */


/**
 * Form settings
 */

$aSettings = [
    'group_name' => [
        'type' => 'string',
        'label' => gT('Name'),
        'htmlOptions' => [
            'size' => 50,
            'maxlength' => 20,
            'id' => 'group_name',
            'name' => 'group_name',
            'required' => 'required',
            'autofocus' => 'autofocus',
        ]
    ],

    'group_description' => [
        'type' => 'text',
        'label' => gT('Description'),
        'htmlOptions' => [
            'cols' => 50,
            'rows' => 4,
            'id' => 'group_description',
            'name' => 'group_description',
        ],
    ],
];


/**
 * Form submit button
 */

$aButtons = [
    gT('Add group') => [
        'type' => 'submit',
        'htmlOptions' => [
            'name' => 'action',
            'value' => 'usergroupindb',
        ],
    ],
];

/**
 * Call the settings widget
 */

$this->widget('ext.SettingsWidget.SettingsWidget', array(
    'title' => gT("Add user group"),
    'form' => true,
    'action' => '/index.php/admin/usergroups/sa/add',
    'formHtmlOptions' => array(
        'id' => 'usergroupform',
        'class' => 'form30',
    ),
    'settings' => $aSettings,
    'buttons' => $aButtons,
));


?>