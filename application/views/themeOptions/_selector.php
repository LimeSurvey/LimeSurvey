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
            /*  this makes only sense if feature "change question theme attributes" is implemented ...
            array(
                //li element
                'type' => 'action',
                'action' => 'reset',
                'url' => App()->createUrl('themeOptions/resetMultiple/'),
                'iconClasses' => '',
                'text' => gT('Reset'),
                'grid-reload' => 'yes',

                //modal
                'actionType' => 'modal',
                'modalType'     => 'yes-no',
                'keepopen'      => 'yes',
                'showSelected'  => 'yes',
                'selectedUrl'   => App()->createUrl('themeOptions/selectedItems/'),
                'yes'           => gT('Reset Themes'),
                'no'            => gT('Cancel'),
                'sModalTitle'   => gT('Reset theme'),
                'htmlModalBody' =>gT('Are you sure you want to reset the selected themes?')
            ),*/
            //uninstall 
            array(
                //li element
                'type'        => 'action',
                'action'      => 'Uninstall',
                'url'         =>  App()->createUrl('themeOptions/uninstallMultiple/'),
                'iconClasses' => '',
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


