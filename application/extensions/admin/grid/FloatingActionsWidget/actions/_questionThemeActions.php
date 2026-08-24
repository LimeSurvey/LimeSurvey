<?php
/**
 * Floating actions for the "Installed question themes" grid.
 */

if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
    return [];
}

return [
    [
        'type'          => 'action',
        'action'        => 'reset',
        'url'           => App()->createUrl('themeOptions/resetMultiple/'),
        'iconClasses'   => 'ri-refresh-line',
        'text'          => gT('Reset'),
        'grid-reload'   => 'yes',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-apply',
        'keepopen'      => 'yes',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
        'yes'           => gT('Reset'),
        'no'            => gT('Cancel'),
        'sModalTitle'   => gT('Reset themes'),
        'htmlModalBody' => gT('Are you sure you want to reset the selected themes?'),
    ],
    [
        'type'          => 'action',
        'action'        => 'Uninstall',
        'url'           => App()->createUrl('themeOptions/uninstallMultiple/'),
        'iconClasses'   => 'ri-delete-bin-fill',
        'btnClass'      => 'floating-delete-button',
        'text'          => gT('Uninstall'),
        'grid-reload'   => 'yes',
        'actionType'    => 'modal',
        'modalType'     => 'cancel-apply',
        'keepopen'      => 'yes',
        'showSelected'  => 'yes',
        'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
        'sModalTitle'   => gT('Uninstall themes'),
        'htmlModalBody' => gT('Are you sure you want to uninstall the selected themes?'),
    ],
];

