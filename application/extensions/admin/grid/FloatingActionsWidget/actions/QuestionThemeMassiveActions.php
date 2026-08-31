<?php

namespace actions;

use Permission;

/**
 * Massive (floating bar) actions for the "Installed question themes" grid.
 *
 * Returns the action definitions consumed by FloatingActionsWidget for the
 * question themes grid. Only the actions the current user is permitted to run are
 * included.
 */
class QuestionThemeMassiveActions
{
    /**
     * @return array
     */
    public static function getActions(): array
    {
        $actions = [];

        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            return $actions;
        }

        // ------------------------------------------------------------------ reset
        $actions[] = [
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
        ];

        // ------------------------------------------------------------------ uninstall
        $actions[] = [
            'type'          => 'action',
            'action'        => 'Uninstall',
            'url'           => App()->createUrl('themeOptions/uninstallMultiple/'),
            'iconClasses'   => 'ri-delete-bin-fill',
            'text'          => gT('Uninstall'),
            'grid-reload'   => 'yes',
            'actionType'    => 'modal',
            'modalType'     => 'cancel-apply',
            'keepopen'      => 'yes',
            'showSelected'  => 'yes',
            'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
            'sModalTitle'   => gT('Uninstall themes'),
            'htmlModalBody' => gT('Are you sure you want to uninstall the selected themes?'),
        ];

        return $actions;
    }
}
