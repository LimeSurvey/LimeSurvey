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
</script>

<div class='menubar' id="participantbar">
    <div class='row container-fluid'>

        <div class="col-md-9">
            <!-- Information -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/index"); ?>" role="button">
                <span class="glyphicon glyphicon-list-alt text-success" ></span>
                <?php eT("Information");?>
            </a>

            <!-- Display participants -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>" role="button">
                <span class="glyphicon glyphicon-list text-success"></span>
                <?php eT("List");?>
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

            <!-- Blacklist control -->
            <?php
            if (Permission::model()->hasGlobalPermission('superadmin','read')):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/blacklistControl"); ?>" role="button">
                    <span class="glyphicon glyphicon-ban-circle text-warning"></span>
                    <?php eT("Blacklist");?>
                </a>

            <!-- Global participant settings -->
            <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/userControl"); ?>" role="button">
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
                &nbsp;&nbsp;
                <?php eT('Return to admin home'); ?>
            </a>
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
<div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
    <?php eT("There are no participants to be exported."); ?>
</div>
<div id="exportcsv" title="exportcsv" style="display:none" class='form30'>
    <ul>
        <li>
            <label for='attributes'><?php eT('Attributes to export:');?></label>
            <select id="attributes" name="attributes" multiple="multiple" style='width: 350px' size=7>
                <?php
                foreach ($aAttributes as $value)
                {
                    echo "<option value=" . $value['attribute_id'] . ">" . $value['defaultname'] . "</option>\n";
                }
                ?>
            </select>
        </li>
    </ul>
</div>
