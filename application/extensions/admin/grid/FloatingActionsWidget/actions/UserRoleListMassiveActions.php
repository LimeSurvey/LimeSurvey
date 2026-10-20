<?php

namespace actions;

/**
 * Action definitions for the User Roles floating action bar.
 */
class UserRoleListMassiveActions
{
    /**
     * @return array
     */
    public static function getActions(): array
    {
        return [
            [
                'type'               => 'action',
                'action'             => 'batchExport',
                'url'                => \App()->createUrl('userRole/batchExport'),
                'iconClasses'        => 'ri-download-line',
                'text'               => \gT('Bulk export roles'),
                'grid-reload'        => 'no',
                'actionType'         => 'redirect',
                'aLinkSpecificDatas' => [
                    'input-name'      => 'sItems',
                    'input-separator' => ',',
                    'target'          => '_self',
                ],
            ],
            [
                'type'          => 'action',
                'action'        => 'delete',
                'url'           => \App()->createUrl('userRole/batchDelete'),
                'iconClasses'   => 'ri-delete-bin-fill',
                'btnClass'      => 'floating-delete-button',
                'text'          => \gT('Delete'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-delete',
                'keepopen'      => 'yes',
                'sModalTitle'   => \gT('Delete roles'),
                'htmlModalBody' => \gT('Are you sure you want to delete the selected role(s)?'),
            ],
        ];
    }
}

