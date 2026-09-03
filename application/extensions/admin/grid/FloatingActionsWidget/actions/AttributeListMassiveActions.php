<?php
namespace actions;

/**
 * Action definitions for the participant attributes floating action bar.
 */
class AttributeListMassiveActions
{
    /**
     * Return the action definitions for the attribute-grid floating bar.
     *
     * @return array
     */
    public static function getActions(): array
    {
        $buttons = [];

        // delete
        if (\Permission::model()->hasGlobalPermission('participantpanel', 'delete')) {
            $buttons[] = [
                'type'          => 'action',
                'action'        => 'delete',
                'url'           => \App()->createUrl('/admin/participants/sa/deleteAttributes'),
                'iconClasses'   => 'ri-delete-bin-fill text-danger',
                'text'          => \gT('Delete'),
                'grid-reload'   => 'yes',
                'actionType'    => 'modal',
                'modalType'     => 'cancel-delete',
                'keepopen'      => 'no',
                'sModalTitle'   => \gT('Delete'),
                'htmlModalBody' => \gT('Do you really want to delete this attribute?'),
                'aCustomDatas'  => [],
            ];
        }

        return $buttons;
    }
}