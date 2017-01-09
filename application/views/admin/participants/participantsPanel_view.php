<script src="<?php echo Yii::app()->getConfig('adminscripts') . "participantpanel.js" ?>" type="text/javascript"></script>

<script type="text/javascript">
    var exporttocsvcountall = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/exporttocsvcountAll"); ?>";
    var exporttocsvall = "<?php echo Yii::app()->getController()->createUrl("exporttocsvAll"); ?>";
    var okBtn = "<?php eT("OK", 'js') ?>";
    var error = "<?php eT("Error", 'js') ?>";
    var exportBtn = "<?php eT("Export", 'js') ?>";
    var cancelBtn = "<?php eT("Cancel", 'js') ?>";
    var sSelectAllText = "<?php eT("Select all", 'js') ?>";
    var sNonSelectedText = "<?php eT("None selected", 'js') ?>";
    var sNSelectedText = "<?php eT("selected", 'js') ?>";
    var exportToCSVURL = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/exporttocsv"); ?>";
    var openModalParticipantPanel = "<?php echo ls\ajax\AjaxHelper::createUrl("/admin/participants/sa/openModalParticipantPanel"); ?>";
    var editValueParticipantPanel = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/editValueParticipantPanel"); ?>";
    
    var translate_blacklisted = "<?php echo '<i class=\"fa fa-undo\"></i> '.gT('Remove from blacklist?'); ?>";
    var translate_notBlacklisted = "<?php echo '<i class=\"fa fa-ban\"></i> '.gT('Add to blacklist?'); ?>";
    var datepickerConfig =     <?php 
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        echo json_encode(array(
            'dateformatdetails'      => $dateformatdetails['dateformat'],
            'dateformatdetailsjs'    => $dateformatdetails['jsdate'],
            "initDatePickerObject" => array(
                "format" => $dateformatdetails['jsdate'],
                "tooltips" => array(
                    "today" => gT('Go to today'),
                    "clear" => gT('Clear selection'),
                    "close" => gT('Close the picker'),
                    "selectMonth" => gT('Select month'),
                    "prevMonth" => gT('Previous month'),
                    "nextMonth" => gT('Next month'),
                    "selectYear" => gT('Select year'),
                    "prevYear" => gT('Previous year'),
                    "nextYear" => gT('Next year'),
                    "selectDecade" => gT('Select decade'),
                    "prevDecade" => gT('Previous decade'),
                    "nextDecade" => gT('Next decade'),
                    "prevCentury" => gT('Previous century'),
                    "nextCentury" => gT('Next century')
                )
            )
        ));?>;
</script>
<div class="menubar surveymanagerbar">
    <div class="row container-fluid">
        <div class="col-xs-12 col-md-12">
            <h3 ><?php eT("Central participant database")?></h3>
        </div>
    </div>
</div>
<div class='menubar surveybar' id="participantbar">
    <div class='row'>

        <div class="col-md-9">
            <!-- Display participants -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>" role="button">
                <span class="glyphicon glyphicon-list text-success"></span>
                <?php eT("Display CPDB participants");?>
            </a>

            <!-- Information -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/index"); ?>" role="button">
                <span class="glyphicon glyphicon-list-alt text-success" ></span>
                <?php eT("Info");?>
            </a>

            <!-- Import from CSV file -->
            <?php
            if (Permission::model()->hasGlobalPermission('participantpanel','import')): ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/importCSV"); ?>" role="button">
                    <span class="icon-importcsv text-success"></span>
                    <?php eT("Import");?>
                </a>
                <?php endif;?>


            <!-- Export to CSV file -->
            <?php
            if (Permission::model()->hasGlobalPermission('participantpanel','export')): ?>
                <?php if (isset($totalrecords) && $totalrecords > 0): ?>
                    <a id="export" class="btn btn-default" href="#" role="button">
                        <span class="icon-exportcsv text-success"></span>
                        <?php eT("Export");?>
                    </a>
                    <?php else:?>
                    <span  title="<?php eT('No participants');?>" data-toggle="tooltip" data-placement="bottom" style="display: inline-block">
                        <a id="export" class="btn btn-default disabled" role="button">
                            <span class="icon-exportcsv text-success"></span>
                            <?php eT("Export");?>
                        </a>
                    </span>
                    <?php endif;?>
                <?php endif;?>

            <?php if (Permission::model()->hasGlobalPermission('superadmin','read')):?>

                <!-- Global participant settings -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/blacklistControl"); ?>" role="button">
                    <span class="icon-global text-success"></span>
                    <?php eT("Settings");?>
                </a>

                <!-- Attribute management -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/attributeControl"); ?>" role="button">
                    <span class="glyphicon glyphicon-tag text-success"></span>
                    <?php eT("Attributes");?>
                </a>

            <?php endif;?>

            <!-- Share panel -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/sharePanel"); ?>" role="button">
                <span class="glyphicon glyphicon-share text-success"></span>
                <?php eT("Share panel");?>
            </a>

        </div>




        <div class="col-md-3 text-right">
            <a class="btn btn-default" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                <span class="glyphicon glyphicon-backward"></span>
                &nbsp;
                <?php eT('Return to admin home'); ?>
            </a>
        </div>
    </div>
</div>

<!-- Modal for editing participants-->
<div class="modal fade" id="participantPanel_edit_modal" tabindex="-1" role="dialog" aria-labelledby="participantPanel_edit_modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
     
    </div>
  </div>
</div>


<div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
    <p><?php eT('Please wait, loading data...');?></p>
    <div class="preloader loading">
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
    </div>
</div>
<div id='exportcsvallnorow' title='exportcsvallnorow' role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php eT("Export participants"); ?></h4>
            </div>
            <div class="modal-body">
                <?php eT("There are no participants to be exported."); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php // TODO: Move modal to separate view ?>
<div id="exportcsv" title="exportcsv" role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php eT("Export participants"); ?> </h4>
            </div>
            <div class="modal-body">
                <div class="form form-horizontal">
                    <div class='form-group'>
                        <label class='control-label col-sm-4' for='attributes'><?php eT('Attributes to export:');?></label>
                        <div class='col-sm-8'>
                            <select id="attributes" name="attributes" multiple="multiple" style='width: 350px' size=7>
                                <?php
                                foreach ($aAttributes as $value)
                                {
                                    echo "<option value=" . $value['attribute_id'] . ">" . $value['defaultname'] . "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php if (Yii::app()->getConfig('hideblacklisted') != 'N'): ?>
                    <div class='alert alert-info'>
                        <p><span class='fa fa-info-circle'></span>&nbsp;<?php eT('If you want to export blacklisted participants, set "Hide blacklisted participants" to "No" in CPDB settings.'); ?></p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT('Close'); ?></button>
                <button type="button" class="btn btn-default exportButton"><?php eT('Export'); ?></button>
            </div>
        </div>
    </div>
</div>
