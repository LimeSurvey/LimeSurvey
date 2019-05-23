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
            <div class="h2"><?php eT("Central participant database")?></div>
        </div>
    </div>
</div>
<div class='menubar surveybar' id="participantbar">
    <div class='row'>

        <div class="col-md-9">
            <?php if (Permission::model()->hasGlobalPermission('participantpanel','read')):?>
                <!-- Display participants -->
                <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>" role="button">
                    <span class="fa fa-list text-success"></span>
                    <?php eT("Display CPDB participants");?>
                </a>
            <?php elseif (Permission::model()->hasGlobalPermission('participantpanel','create')
                || ParticipantShare::model()->exists('share_uid = :userid', [':userid' => App()->user->id])):?>
                <!-- Display my participants -->
                <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>" role="button">
                    <span class="fa fa-list text-success"></span>
                    <?php eT("Display my CPDB participants");?>
                </a>
            <?php endif;?>

            <!-- Information -->
            <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/index"); ?>" role="button">
                <span class="fa fa-list-alt text-success" ></span>
                <?php eT("Info");?>
            </a>

            <!-- Import from CSV file -->
            <?php
            if (Permission::model()->hasGlobalPermission('participantpanel','import')): ?>
                <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/importCSV"); ?>" role="button">
                    <span class="icon-importcsv text-success"></span>
                    <?php eT("Import");?>
                </a>
                <?php endif;?>

            <?php if (Permission::model()->hasGlobalPermission('superadmin','read')):?>

                <!-- Global participant settings -->
                <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/blacklistControl"); ?>" role="button">
                    <span class="icon-global text-success"></span>
                    <?php eT("Blacklist settings");?>
                </a>

                <!-- Attribute management -->
                <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/attributeControl"); ?>" role="button">
                    <span class="fa fa-tag text-success"></span>
                    <?php eT("Attributes");?>
                </a>

            <?php endif;?>

            <!-- Share panel -->
            <a class="btn btn-default pjax" href="<?php echo $this->createUrl("admin/participants/sa/sharePanel"); ?>" role="button">
                <span class="fa fa-share text-success"></span>
                <?php eT("Share panel");?>
            </a>

            <!-- Export to CSV file -->
            <?php
            if (Permission::model()->hasGlobalPermission('participantpanel','export')): ?>

                    <a id="export" class="btn btn-default" href="#" role="button">
                        <span class="icon-exportcsv text-success"></span>
                        <?php eT("Export all participants");?>
                    </a>

            <?php endif;?>
        </div>




        <div class="col-md-3 text-right">
            <a class="btn btn-default" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                <span class="fa fa-backward"></span>
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

<?php
    $aModalData = ['aAttributes' => $aAttributes];
    App()->getController()->renderPartial('/admin/participants/modal_subviews/_exportCSV', $aModalData);

App()->getClientScript()->registerScript('ParticipantsPanelBSSwitcher', "
    LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>

