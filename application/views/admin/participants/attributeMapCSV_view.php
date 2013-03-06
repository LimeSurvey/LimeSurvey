        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('adminstyleurl') . "attributeMapCSV.css" ?>" />
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.qtip.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.ui.nestedSortable.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('adminscripts') . "attributeMapCSV.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">

            var copyUrl = "<?php echo $this->createUrl("admin/participants/sa/uploadCSV"); ?>";
            var displayParticipants = "<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>";
            var mapCSVcancelled = "<?php echo $this->createUrl("admin/participants/sa/mapCSVcancelled"); ?>";
            var characterset = "<?php echo sanitize_paranoid_string($_POST['characterset']); ?>";
            var okBtn = "<?php $clang->eT("OK") ?>";
            var processed = "<?php $clang->eT("Summary") ?>";
            var summary = "<?php $clang->eT("Upload summary") ?>";
            var notPairedErrorTxt = "<?php $clang->eT("You have to pair this field with an existing attribute.") ?>";
            var onlyOnePairedErrorTxt = "<?php $clang->eT("Only one CSV attribute is mapped with central attribute.") ?>";
            var cannotAcceptErrorTxt="<?php $clang->eT("This list cannot accept token attributes.") ?>";
            var seperator = "<?php echo sanitize_paranoid_string($_POST['seperatorused']); ?>";
            var thefilepath = "<?php echo $fullfilepath ?>";
            var filterblankemails = "<?php echo $filterbea ?>";
        </script>
    </head>
    <body>
        <div class='header ui-widget-header'><strong><?php printf($clang->ngT("Select which fields to import as attributes with your participant.","Select which fields to import as attributes with your %s participants.",$linecount), $linecount); ?></strong></div>
        <div class="main">
            <div id="csvattribute" class='container'>
                <div class="heading"><?php $clang->eT("CSV field names "); ?></div>
                <div class='instructions'><?php $clang->eT("The following additional fields were found in your CSV file."); ?></div>

                <ul class="csvatt">
                    <?php
                    foreach ($firstline as $key => $value)
                    {
                        echo "<li id='cs_" . $value . "' name='cs_" . $value . "' >" . $value . "</li>";
                    }
                    ?>
                </ul>
            </div>
            <div id="newcreated" class='container'><div class="heading"><?php $clang->eT("Attributes to be created") ?></div>
            <div class='instructions'><?php $clang->eT("Drop a CSV field into this area to create a new participant attribute and import your data into it."); ?></div>
                <ul class="newcreate" id="sortable">
                </ul>
            </div>
            <div id="centralattribute" class='container'><div class="heading"><?php $clang->eT("Existing attribute"); ?></div>
            <div class='instructions'><?php $clang->eT("Drop a CSV field into an existing participant attribute listed below to import your data into it."); ?></div>
                <ul class="cpdbatt">
                    <?php
                    foreach ($attributes as $key => $value)
                    {
                        echo "<li id='c_" . $value['attribute_id'] . "' name='c_" . $key . "'>" . $value['attribute_name'] . "<br />&nbsp;</li>";
                    }
                    ?>
                </ul>
            <div class='explanation'>
                <input type='checkbox' id='overwrite' name='overwrite' /> <label for='overwrite'><?php $clang->eT("Overwrite existing token attribute values if a duplicate participant is found?") ?></label>
                <br /><br /><?php
                if($participant_id_exists) {
                    $clang->eT("Duplicates will be detected using the participant_id field in this CSV file");
                } else {
                    $clang->eT("Duplicates will be detected by a combination of firstname, lastname and email addresses");
                }

                ?>
            </div>
            </div>
        </ul>
    </div>
    <p><input type="button" name="attmapcancel" id="attmapcancel" value="<?php $clang->eT("Cancel") ?>" />
        <input type="button" name="attreset" id="attreset" value="<?php $clang->eT("Reset") ?>" onClick="window.location.reload();" />
        <input type="button" name="attmap" id="attmap" value="<?php $clang->eT("Continue"); ?>" />
    </p>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
        <img src="<?php echo Yii::app()->getConfig('adminimageurl') . '/ajax-loader.gif'; ?>" alt="<?php $clang->eT('Loading...'); ?>" title="<?php $clang->eT('Loading...'); ?>" />
    </div>
</body>
</html>
