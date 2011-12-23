<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('styleurl') . "admin/default/adminstyle.css" ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('styleurl') . "admin/default/attributeMapCSV.css" ?>" />
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery-ui.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.qtip.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('generalscripts') . "jquery/jquery.ui.sortable.js" ?>" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->getConfig('adminscripts') . "attributeMapCSV.js" ?>" type="text/javascript"></script>
        <script type="text/javascript">mapCSVcancelled

            var copyUrl = "<?php echo $this->createURL("admin/participants/sa/uploadCSV"); ?>";
            var displayParticipants = "<?php echo $this->createURL("admin/participants/sa/displayParticipants"); ?>";
            var mapCSVcancelled = "<?php echo $this->createURL("admin/participants/sa/mapCSVcancelled"); ?>";
            var characterset = "<?php echo $_POST['characterset']; ?>";
            var okBtn = "<?php $clang->eT("OK") ?>";
            var processed = "<?php $clang->eT("Summary") ?>";
            var summary = "<?php $clang->eT("Upload summary") ?>";
            var seperator = "<?php echo $_POST['seperatorused']; ?>";
            var thefilepath = "<?php echo $fullfilepath ?>";
        </script>
    </head>
    <body>
        <div class='header ui-widget-header'><strong><?php printf($clang->gT("Select attributes to copy with your %s participant(s)"), $linecount); ?></strong></div>
        <div class="main">
            <div id="csvattribute">
                <div class="heading"><?php $clang->eT("CSV headings "); ?></div>
                <ul class="csvatt">
                    <?php
                    foreach ($firstline as $key => $value)
                    {
                        echo "<li id='cs_" . $value . "' name='cs_" . $value . "' >" . $value . "</li>";
                    }
                    ?>
                </ul>
            </div>
            <div id="newcreated"><div class="heading"><?php $clang->eT("Attributes to be created") ?></div>
                <ul class="newcreate" id="sortable" style ="height: 40px">
                </ul>
            </div>
            <div id="centralattribute"><div class="heading"><?php $clang->eT("Central attribute"); ?></div>
                <ul class="cpdbatt">
                    <?php
                    foreach ($attributes as $key => $value)
                    {
                        echo "<li id='c_" . $value['attribute_id'] . "' name='c_" . $key . "' >" . $value['attribute_name'] . "</li>";
                    }
                    ?>
                </ul>
            </div>
        </ul>
    </div>
    <p><input type="button" name="attmap" id="attmap" value="Continue" />
        <input type="button" name="attmapcancel" id="attmapcancel" value="Cancel" />
    </p>
    <div id="processing" title="<?php $clang->eT("Processing...") ?>" style="display:none">
        <img src="<?php echo Yii::app()->getConfig('imageurl') . '/ajax-loader.gif'; ?>" alt="<?php $clang->eT('Loading...'); ?>" title="<?php $clang->eT('Loading...'); ?>" />
    </div>
</body>
</html>