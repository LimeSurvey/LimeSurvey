<!-- Rendering massive action widget -->
<div class="col-sm-4 pull-left dropup listActions">
    <!-- Drop Up button selector -->
    <button class='btn btn-default dropdown-toggle' id='massive-action-dropdown-selector' type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
    <span id='massive-action-dropdown-selector-text'><?php eT('Filtered participant(s)'); ?></span>
        <span class='caret'></span>
    </button>

    <!-- List of actions -->
    <ul class='dropdown-menu listActions'>

        <!-- Header -->
        <li class='dropdown-header'></li>

        <!-- Delete -->
        <li>
            <a href='#' data-toggle='modal' data-target='#delete-option-modal'>
                <span class='text-danger glyphicon glyphicon-trash'></span>
                <?php eT('Delete'); ?>
            </a>
        </li>

        <li role='separator' class='divider'></li>

        <li>
            <a href='#' onclick='LS.CPDB.onClickExport();'>
                <span class='icon-exportcsv'></span>
                <?php eT('Export'); ?>
            </a>
        </li>
        <li>
            <a href='#'>
                <span class='fa fa-share'></span>
                <?php eT('Share'); ?>
            </a>
        </li>
        <li>
            <a href="#">
                <span class='fa fa-user-plus'></span>
                <?php eT('Add participants to survey'); ?>
            </a>
        </li>
    </ul>
</div>

<!-- Modal for delete -->
<div class="modal fade" id="delete-option-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php eT('Delete one or more participants...'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php eT('Please choose one option.'); ?> </p>
                <select id='delete-participant-select-option' class="form-control post-value">
                    <option value="po" selected><?php eT("Delete only from the central panel"); ?></option>
                    <option value="pt"><?php eT("Delete from the central panel and associated surveys"); ?></option>
                    <option value="ptta"><?php eT("Delete from central panel, associated surveys and all associated responses"); ?></option>
                </select>
            </div>
            <div class="modal-footer">
                <a 
                    onclick='LS.CPDB.deleteParticipant("<?php echo App()->createUrl('/admin/participants/sa/deleteParticipant/'); ?>");'
                    class="btn btn-ok btn-danger"
                    data-dismiss='modal'
                >
                    <span class="fa fa-trash"></span>&nbsp;<?php eT('Delete'); ?>
                </a>
                <a class="btn btn-default" data-dismiss="modal"><?php eT('Cancel'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php

/*
$this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
    'pk'          => 'selectedParticipant',
    'gridid'      => 'list_central_participants',
    'dropupId'    => 'tokenListActions',
    'dropUpText'  => gT('All participant(s)...'),

    'aActions'    => array(
        // Delete
        array(
            // li element
            'type'        => 'action',
            'action'      => 'delete',
            'url'         =>  App()->createUrl('/admin/participants/sa/deleteParticipant/'),
            'iconClasses' => 'text-danger glyphicon glyphicon-trash',
            'text'        =>  gT('Delete'),
            'grid-reload' => 'yes',
            'allow-no-selected' => 'yes',
            'on-success'  => "(function(result) { LS.ajaxHelperOnSuccess(result); })",

            // Modal
            'actionType'    => 'modal',
            'modalType'     => 'empty',
            'keepopen'      => 'no',
            'sModalTitle'   => gT('Delete one or more participants...'),
            'htmlModalBody' => 
                '<p>' . gT('Please choose one option.') . '</p>' .
                // The class 'post-value' will make widget post input/select to controller url
                '<select name="selectedoption" class="form-control post-value">
                        <option value="po" selected>' . gT("Delete only from the central panel") . '</option>
                        <option value="pt">' . gT("Delete from the central panel and associated surveys") . '</option>
                        <option value="ptta">' . gT("Delete from central panel, associated surveys and all associated responses") . '</option>
                </select>',
            'htmlFooterButtons'   => array(
                // The class 'btn-ok' binds to URL above
                '<a class="btn btn-ok btn-danger"><span class="fa fa-trash"></span>&nbsp;' . gT('Delete') . '</a>',
                '<a class="btn btn-default" data-dismiss="modal">' . gT('Cancel') . '</a>'
            ),
            'aCustomDatas'  => array(
            ),
        ),

        // Separator
        array('type'  => 'separator'),

        // Export
        array(
            'type' => 'action',
            'action' => 'export',
            'url' => '',  // Not relevant
            'iconClasses' => 'icon-exportcsv',
            'text' => gT('Export'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function() { LS.CPDB.onClickExport(); })'
        ),
        // Share
        array(
            'type' => 'action',
            'action' => 'share',
            'url' => '',  // Not relevant
            'iconClasses' => 'fa fa-share',
            'text' => gT('Share'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function(itemIds) { LS.CPDB.shareMassiveAction(itemIds); })'
        ),
        // Add to survey
        array(
            'type' => 'action',
            'action' => 'add-to-survey',
            'url' => '',  // Not relevant
            'iconClasses' => 'fa fa-user-plus',
            'text' => gT('Add participants to survey'),
            'grid-reload' => 'no',

            'actionType' => 'custom',
            'custom-js' => '(function(itemIds) { LS.CPDB.addParticipantToSurvey(itemIds); })'
        )
    )
));
 */

?>
