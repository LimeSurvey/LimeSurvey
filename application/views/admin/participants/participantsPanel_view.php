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
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/summary.png" />
                    <?php eT("Information");?>
                </a>

                <!-- Display participants -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/surveylist.png" />
                    <?php eT("List");?>
                </a>

                <!-- Import from CSV file -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/importCSV"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/importcsv.png" />
                    <?php eT("Import");?>
                </a>

                <!-- Export to CSV file -->
                <a id="export" class="btn btn-default" href="#" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/exportcsv.png" />
                    <?php eT("Export");?>
                </a>

                <!-- Blacklist control -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/blacklistControl"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/trafficred.png" />
                    <?php eT("Blacklist");?>
                </a>                

                <!-- Global participant settings -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/userControl"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/global.png" />
                    <?php eT("Settings");?>
                </a>

                <!-- Attribute management -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/attributeControl"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/tag.png" />
                    <?php eT("Attributes");?>
                </a>                

                <!-- Share panel -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/participants/sa/sharePanel"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/share.png" />
                    <?php eT("Share panel");?>
                </a>                                                

        </div>




        <div class="col-md-3 text-right">
            <a class="btn btn-default" href="<?php echo $this->createUrl('admin/survey/sa/index'); ?>" role="button">
                <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
                &nbsp;&nbsp;
                <?php eT('return to admin pannel'); ?>
            </a>
        </div>
    </div>
</div>


<!-- TODO : check and refactore -->
<div id='exportcsvallprocessing' title='exportcsvall' style='display:none'>
    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/ajax-loader.gif" alt="AJAX loader" />    
</div>
<div id='exportcsvallnorow' title='exportcsvallnorow' style='display:none'>
    <?php eT("There are no participants to be exported."); ?>    
</div>
<div id="exportcsv" title="exportcsv" style="display:none" class='form30'>
    <ul>
        <li>
            <label for='attributes'>Attributes to export:</label>
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