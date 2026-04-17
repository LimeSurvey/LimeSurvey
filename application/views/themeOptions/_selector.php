<?php
/**
 * Render the selector for Surveythemes massive actions.
 * @var string $gridID
 * @var string $dropupID
 * @var string $pk
 */
?>

<!-- Rendering massive action widget -->
<?php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
        'pk'          => $pk,
        'gridid'      => $gridID,
        'dropupId'    => $dropupID,
        'dropUpText'  => gT('Selected theme(s)...'),
        'aActions'    => array(
            //reset
            array(
                //li element
                'type' => 'action',
                'action' => 'reset',
                'url' => App()->createUrl('themeOptions/resetMultiple/'),
                'iconClasses' => 'ri-refresh-line',
                'text' => gT('Reset'),
                'grid-reload' => 'yes',

                //modal
                'actionType'    => 'modal',
                'modalType'     => 'cancel-apply',
                'keepopen'      => 'yes',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
                'yes'           => gT('Reset'),
                'no'            => gT('Cancel'),
                'sModalTitle'   => gT('Reset themes'),
                'htmlModalBody' => gT('Are you sure you want to reset the selected themes?'),
            ),
            //uninstall 
            array(
                //li element
                'type'        => 'action',
                'action'      => 'Uninstall',
                'url'         =>  App()->createUrl('themeOptions/uninstallMultiple/'),
                'iconClasses' => 'ri-delete-bin-fill',
                'text'        =>  gT('Uninstall'),
                'grid-reload' => 'yes',

                // modal
                'actionType'    => 'modal',
                'modalType'     => 'cancel-apply',
                'keepopen'      => 'yes',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
                'sModalTitle'   => gT('Uninstall themes'),
                'htmlModalBody' => gT('Are you sure you want to uninstall the selected themes?'),
            )
        )
    ));



    
?>


